<?php

namespace App\Http\Controllers;

use App\Models\BrandSprav;
use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateController extends Controller
{

    public function index()
    {
        $timeXML = Update::find(1);
        $timeYAML = Update::find(2);
        return view('update', compact('timeXML', 'timeYAML'));
    }
    public function updateXML()
    {
        set_time_limit(3600);
        try {
            echo 'Выполнение обновление файла XML';
            ini_set('max_execution_time', 0); // Без ограничения по времени
            // 1. Загрузка первого XML
            Log::info('Загрузка первого XML начата');
            $response1 = Http::withoutVerifying()->get('https://prdownload.nodacdn.net/dfiles/7da749ad-284074-7b2184d7/articles.xml');
            Log::info('Загрузка первого XML завершена');

            if (!$response1->ok()) {
                throw new \Exception('Ошибка при загрузке первого XML: ' . $response1->status());
            }

            $xmlContent1 = str_replace('&nbsp;', ' ', $response1->body());
            $xml1 = simplexml_load_string($xmlContent1);

            // 2. Загрузка второго XML
            Log::info('Загрузка второго XML начата');
            $response2 = Http::withoutVerifying()->get('https://www.buszap.ru/get_price?p=28eb21146a7944a9abd330fbf916aa7c&FranchiseeId=9117065');
            Log::info('Загрузка второго XML завершена');

            if (!$response2->ok()) {
                throw new \Exception('Ошибка при загрузке второго XML: ' . $response2->status());
            }

            $xmlContent2 = str_replace('&nbsp;', ' ', $response2->body());
            $xml2 = simplexml_load_string($xmlContent2);

            // 3. Обработка первого XML
            $this->processXML($xml1);

            // 4. Обработка второго XML
            $this->processXML($xml2);

            // 5. Объединение данных
            foreach ($xml2->Ad as $ad) {
                $adClone = $xml1->addChild('Ad');
                foreach ($ad->children() as $child) {
                    $adClone->addChild($child->getName(), (string) $child);
                }
            }

            // 6. Сохранение объединенного XML
            Log::info('Объединенный XML сохраняется...');
            $fileName = 'merged_articles.xml';
            $xmlContent = $xml1->asXML();

            Storage::disk('public')->put($fileName, $xmlContent);
            Log::info('Объединенный XML успешно сохранен');
            $update = Update::find(1);
            if ($update) {
                $update->date_update = now();
                $update->save();
            }

            return redirect()->route('update')->with('success', 'Объединенный XML успешно сохранен!');
        } catch (\Exception $e) {
            Log::error('Ошибка в ProcessArticles: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('update')->with('error', 'Произошла ошибка: ' . $e->getMessage());
        }
    }

    private function processXML(&$xml)
    {
        set_time_limit(3600);
        foreach ($xml->Ad as $ad) {
            Log::info('Обрабатываем Ad ID: ' . (string) $ad->Id);
            $adId = explode('_', (string) $ad->Id);
            $articul0 = $adId[1];
            $brand00 = $adId[0];

            // $brands = BrandSprav::where('brand', '=', $brand00)
            //     ->orWhere('sprav', 'LIKE', '%' . $brand00 . '%')
            //     ->get();

            // if ($brands) {
            //     foreach ($brands as $brand0) {
            //         // Получение данных из базы
            //         $rows = DB::table('images')
            //             ->where('brand', $brand0)
            //             ->where('articul', 'LIKE', '%' . $articul0 . '%')
            //             ->get();

            //         if ($rows->isNotEmpty()) {
            //             unset($ad->Images->Image);

            //             foreach ($rows as $row) {
            //                 $path = "https://233204.fornex.cloud/uploads/" . strtolower($row->brand) . "/" . strtolower($row->articul);
            //                 $newImage = $ad->Images->addChild('Image', ' ');
            //                 $newImage->addAttribute('url', $path);
            //             }
            //         }

            //         // Запрос цены
            //         $priceResponse = Http::withoutVerifying()->get("https://abcp50533.public.api.abcp.ru/search/articles/", [
            //             'userlogin' => 'api@abcp50533',
            //             'userpsw'   => '6f42e31351bc2469f37f27a7fa7da37c',
            //             'number'    => $articul0,
            //             'brand'     => $brand0,
            //         ]);

            //         if ($priceResponse->ok()) {
            //             $priceData = json_decode($priceResponse->body(), true);

            //             foreach ($priceData as $data) {
            //                 if (
            //                     isset($data['distributorId'], $data['brand'], $data['number']) &&
            //                     $data['distributorId'] == '1664240' &&
            //                     $data['brand'] == $brand0 &&
            //                     $data['number'] == $articul0
            //                 ) {
            //                     if (isset($ad->Price, $data['price'])) {
            //                         unset($ad->Price);
            //                         $ad->addChild('Price', $data['price']);
            //                         break;
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // } else { // Получение данных из базы
            $brand0 = $brand00;
            $rows = DB::table('images')
                ->where('brand', $brand0)
                ->where('articul', 'LIKE', '%' . $articul0 . '%')
                ->get();

            if ($rows->isNotEmpty()) {
                unset($ad->Images->Image);

                foreach ($rows as $row) {
                    $path = "https://233204.fornex.cloud/uploads/" . strtolower($row->brand) . "/" . strtolower($row->articul);
                    $newImage = $ad->Images->addChild('Image', ' ');
                    $newImage->addAttribute('url', $path);
                }
            }

            // Запрос цены
            $priceResponse = Http::withoutVerifying()->get("https://abcp50533.public.api.abcp.ru/search/articles/", [
                'userlogin' => 'api@abcp50533',
                'userpsw'   => '6f42e31351bc2469f37f27a7fa7da37c',
                'number'    => $articul0,
                'brand'     => $brand0,
            ]);

            if ($priceResponse->ok()) {
                $priceData = json_decode($priceResponse->body(), true);

                foreach ($priceData as $data) {
                    if (
                        isset($data['distributorId'], $data['brand'], $data['number']) &&
                        $data['distributorId'] == '1664240' &&
                        $data['brand'] == $brand0 &&
                        $data['number'] == $articul0
                    ) {
                        if (isset($ad->Price, $data['price'])) {
                            unset($ad->Price);
                            $ad->addChild('Price', $data['price']);
                            break;
                        }
                    }
                }
            }
            // }
        }
    }

    public function updateYaml()
    {
        set_time_limit(3600);
        try {
            echo 'Выполнение обновление файла YAML';
            ini_set('max_execution_time', 0); // Убрать ограничения на выполнение

            // 1. Загрузка первого YAML
            Log::info('Загрузка первого YAML начата');
            $response1 = Http::withoutVerifying()->get('https://www.buszap.ru/get_price?p=219a76583bbd4991ade213a8b15b5808&FranchiseeId=9117065');
            Log::info('Загрузка первого YAML завершена');

            if (!$response1->ok()) {
                throw new \Exception('Ошибка при загрузке первого YAML: ' . $response1->status());
            }

            $yaml1 = simplexml_load_string($response1->body())->shop->offers;

            // 2. Загрузка второго YAML
            Log::info('Загрузка второго YAML начата');
            $response2 = Http::withoutVerifying()->get('https://www.buszap.ru/get_price?p=3dbb37d4f12242068faf72c2cf839c82&FranchiseeId=9117065');
            Log::info('Загрузка второго YAML завершена');

            if (!$response2->ok()) {
                throw new \Exception('Ошибка при загрузке второго YAML: ' . $response2->status());
            }

            $yaml2 = simplexml_load_string($response2->body())->shop->offers;

            // 3. Обработка первого YAML
            Log::info('Обработка первого YAML начата');
            $this->processOffers($yaml1);

            // 4. Обработка второго YAML
            Log::info('Обработка второго YAML начата');
            $this->processOffers($yaml2);

            // 5. Объединение данных
            Log::info('Объединение YAML данных начато');
            foreach ($yaml2->offer as $offer) {
                $offerClone = $yaml1->addChild('offer');
                foreach ($offer->attributes() as $attr => $value) {
                    $offerClone->addAttribute($attr, $value);
                }
                foreach ($offer->children() as $child) {
                    $offerClone->addChild($child->getName(), (string) $child);
                }
            }
            Log::info('Объединение YAML данных завершено');

            // 6. Сохранение объединенного YAML
            $fileName = 'merged_offers.yaml';
            $yamlContent = $yaml1->asXML();
            Storage::disk('public')->put($fileName, $yamlContent);

            Log::info('Объединенный YAML успешно сохранен');
            $update = Update::find(2);
            if ($update) {
                $update->date_update = now();
                $update->save();
            }
            return redirect()->route('update')->with('success', 'Объединенный YAML успешно сохранен!');
        } catch (\Exception $e) {
            Log::error('Ошибка в updateYaml: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('update')->with('error', 'Произошла ошибка: ' . $e->getMessage());
        }
    }

    private function processOffers(&$offers)
    {
        set_time_limit(3600);
        foreach ($offers->offer as $offer) {
            $id = (string) $offer['id'];
            [$articul, $brand] = explode('/', $id);

            // Обновление изображения
            Log::info("Обработка изображения для: Бренд = $brand, Артикул = $articul");
            $rows = DB::table('images')
                ->where('brand', $brand)
                ->where('articul', 'LIKE', '%' . $articul . '%')
                ->get();

            if ($rows->isNotEmpty()) {
                if (isset($offer->picture)) {
                    unset($offer->picture);
                }

                foreach ($rows as $row) {
                    $path = "https://233204.fornex.cloud/uploads/" . strtolower($row->brand) . "/" . strtolower($row->articul);
                    $offer->addChild('picture', $path);
                }
            }

            // Запрос цены
            Log::info("Запрос цены для: Бренд = $brand, Артикул = $articul");
            $priceResponse = Http::withoutVerifying()->get("https://abcp50533.public.api.abcp.ru/search/articles/", [
                'userlogin' => 'api@abcp50533',
                'userpsw'   => '6f42e31351bc2469f37f27a7fa7da37c',
                'number'    => $articul,
                'brand'     => $brand,
            ]);

            if ($priceResponse->ok()) {
                $priceData = json_decode($priceResponse->body(), true);

                foreach ($priceData as $data) {
                    if (
                        isset($data['distributorId'], $data['brand'], $data['number']) &&
                        $data['distributorId'] == '1664240' &&
                        $data['brand'] == $brand &&
                        $data['number'] == $articul
                    ) {
                        if (isset($offer->price, $data['price'])) {
                            $offer->price = $data['price'];
                        } else {
                            $offer->addChild('price', $data['price']);
                        }
                        Log::info("Цена обновлена для $id: " . $data['price']);
                        break;
                    }
                }
            } else {
                Log::warning("Не удалось получить цену для: Бренд = $brand, Артикул = $articul");
            }
        }
    }
}
