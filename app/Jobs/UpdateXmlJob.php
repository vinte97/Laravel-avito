<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateXmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            ini_set('max_execution_time', 0); // Без ограничения по времени
            // 1. Загрузка XML
            Log::info('Загрузка XML начата');
            $response = Http::withoutVerifying()->get('https://prdownload.nodacdn.net/dfiles/7da749ad-284074-7b2184d7/articles.xml');
            Log::info('Загрузка XML завершена');

            if (!$response->ok()) {
                throw new \Exception('Ошибка при загрузке XML: ' . $response->status());
            }

            $xml = simplexml_load_string($response->body());

            // 2. Обработка каждого Ad
            foreach ($xml->Ad as $ad) {
                Log::info('Обрабатываем Ad ID: ' . (string) $ad->Id);
                $adId = explode('_', (string) $ad->Id);

                // 3. Получение данных из базы
                $rows = DB::table('images')
                    ->where('brand', $adId[0])
                    ->where('articul', 'LIKE', '%' . $adId[1] . '%')
                    ->get();

                if ($rows->isNotEmpty()) {
                    unset($ad->Images->Image);

                    foreach ($rows as $row) {
                        $path = "https://233204.fornex.cloud/uploads/" . strtolower($row->brand) . "/" . strtolower($row->articul);
                        $newImage = $ad->Images->addChild('Image', ' ');
                        $newImage->addAttribute('url', $path);
                    }
                }

                // 4. Запрос цены
                $brand0 = $adId[0];
                $articul0 = $adId[1];
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
            }

            // 5. Сохранение XML через Storage
            Log::info('XML сохраняется...');
            $fileName = 'modified_articles_1.xml';
            $xmlContent = $xml->asXML();

            Storage::disk('public')->put($fileName, $xmlContent);
            Log::info('XML успешно сохранен');
        } catch (\Exception $e) {
            Log::error('Ошибка в ProcessArticles: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
