<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BarcodeController extends Controller
{
    public function lookup(string $barcode): JsonResponse
    {
        if (! preg_match('/^\d{8,14}$/', $barcode)) {
            return response()->json(['found' => false, 'reason' => 'invalid_barcode'], 422);
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'MacroBot/1.0 (calorie tracking app)'])
                ->get("https://world.openfoodfacts.org/api/v2/product/{$barcode}.json");
        } catch (\Exception) {
            return response()->json(['found' => false, 'reason' => 'network_error'], 503);
        }

        if (! $response->ok()) {
            return response()->json(['found' => false], 404);
        }

        $data = $response->json();

        if (($data['status'] ?? 0) === 0 || empty($data['product'])) {
            return response()->json(['found' => false], 404);
        }

        $product = $data['product'];
        $nutriments = $product['nutriments'] ?? [];

        $per100g = [
            'calories' => $this->nutrimentValue($nutriments, ['energy-kcal_100g', 'energy-kcal', 'energy_100g']),
            'protein_g' => $this->nutrimentValue($nutriments, ['proteins_100g', 'proteins']),
            'carbs_g' => $this->nutrimentValue($nutriments, ['carbohydrates_100g', 'carbohydrates']),
            'fat_g' => $this->nutrimentValue($nutriments, ['fat_100g', 'fat']),
        ];

        $missingFields = collect($per100g)
            ->filter(fn ($v) => $v === null)
            ->keys()
            ->values()
            ->toArray();

        return response()->json([
            'found' => true,
            'barcode' => $barcode,
            'name' => $product['product_name'] ?? $product['product_name_en'] ?? null,
            'brand' => ! empty($product['brands']) ? explode(',', $product['brands'])[0] : null,
            'image_url' => $product['image_front_small_url'] ?? null,
            'per_100g' => $per100g,
            'serving_size_label' => $product['serving_size'] ?? null,
            'serving_quantity_g' => isset($product['serving_quantity']) && is_numeric($product['serving_quantity'])
                ? (float) $product['serving_quantity']
                : null,
            'calories_missing' => $per100g['calories'] === null,
            'missing_fields' => $missingFields,
        ]);
    }

    private function nutrimentValue(array $nutriments, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($nutriments[$key]) && is_numeric($nutriments[$key])) {
                return round((float) $nutriments[$key], 2);
            }
        }

        return null;
    }
}
