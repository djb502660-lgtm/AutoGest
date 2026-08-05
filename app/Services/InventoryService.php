<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;

class InventoryService
{
    private $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function getInventorySummary()
    {
        return [
            'products' => Product::with(['category', 'brand'])->orderBy('name')->get(),
            'categories' => Category::with('brands')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'total_products' => Product::count(),
            'low_stock' => Product::where('stock_quantity', '<', 10)->count(),
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
        if (! $product) {
            return false;
        }

        $oldStock = $product->stock;
        $product->stock = $quantity;
        $result = $product->save();

        if ($result) {
            $action = $quantity < $oldStock ? 'stock_removed' : 'stock_updated';
            $description = $quantity < $oldStock
                ? "Stock de producto {$product->sku} reducido de {$oldStock} a {$quantity}"
                : "Stock de producto {$product->sku} actualizado de {$oldStock} a {$quantity}";

            $this->auditService->logInventoryAction(
                $action,
                $description,
                auth()->id(),
                ['old_stock' => $oldStock],
                ['new_stock' => $quantity]
            );
        }

        return $result;
    }

    public function getTotalInventoryValue()
    {
        return Product::sum('price');
    }
}
