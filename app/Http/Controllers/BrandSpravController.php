<?php

namespace App\Http\Controllers;

use App\Models\BrandSprav;
use App\Models\Image;
use Illuminate\Http\Request;

class BrandSpravController extends Controller
{
    public function index()
    {
        $brands = Image::select('brand')->distinct('brand')->paginate(10);
        return view("brand", compact("brands"));
    }
    public function view($brand)
    {
        $brand = BrandSprav::where('brand', '=', $brand)->first();
        if ($brand) {
            return response()->json($brand);
        }
        return response()->json(['error' => 404], 200);
    }
    public function AddOrEdit(Request $request)
    {
        $brand = BrandSprav::updateOrCreate(
            ['brand' => $request->brand],
            [
                'brand' => $request->brand,
                'sprav' => $request->sprav
            ]
        );
        if ($brand) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Не удалось создать или обновить запись'], 500);
        }
    }
    public function clear($brand)
    {
        $brand = BrandSprav::where('brand', '=', $brand)->first();
        if ($brand) {
            $brand->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 404]);
    }
}
