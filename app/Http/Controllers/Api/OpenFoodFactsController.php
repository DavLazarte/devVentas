<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OpenFoodFactsController extends Controller
{
    public function findByBarcode($barcode)
    {
        // Cache por 24 horas
        $product = Cache::remember("product_{$barcode}", 86400, function () use ($barcode) {
            $response = Http::withHeaders([
                'User-Agent' => 'MiPOS/1.0 (admin@local.test)'
            ])->get("https://world.openfoodfacts.org/api/v2/product/{$barcode}.json");

            if ($response->failed() || $response->json('status') === 0) {
                return null;
            }

            $p = $response->json('product');

            return [
                'barcode'     => $barcode,
                'name'        => $p['product_name'] ?? null,
                'brand'       => $p['brands'] ?? null,
                'quantity'    => $p['quantity'] ?? null,
                'image_url'   => $p['image_front_url'] ?? null,
                'categories'  => $p['categories'] ?? null,
                'ingredients' => $p['ingredients_text'] ?? null,
                'nutriscore'  => $p['nutriscore_grade'] ?? null,
            ];
        });

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }
}
