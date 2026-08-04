<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Purchase;

class InventoryService
{
    public function getInventorySummary()
    {
        return [
            'products' => Product::with(['category', 'brand'])->orderBy('name')->get(),
            'categories' => Category::with('brands')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'total_products' => Product::count(),
            'low_stock' => Product::where('stock', '<', 10)->count(),
        ];
    }

    public function getRecentPurchases($limit = 10)
    {
        return Purchase::with('supplier')->latest()->take($limit)->get();
    }

    public function getProductBySku($sku)
    {
        return Product::where('sku', $sku)->first();
    }

    public function getProductsByCategory($categoryId)
    {
        return Product::where('category_id', $categoryId)->with(['category', 'brand'])->get();
    }

    public function getProductsByBrand($brandId)
    {
        return Product::where('brand_id', $brandId)->with(['category', 'brand'])->get();
    }

    public function updateStock($productId, $quantity)
    {
        $product = Product::find($productId);
        if (!$product) return false;

        $product->stock = $quantity;
        return $product->save();
    }

    public function getTotalInventoryValue()
    {
        return Product::sum('price');
    }
}
