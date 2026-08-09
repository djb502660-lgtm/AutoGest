<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ServiceOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Tests\TestCase;

class InventoryModelsTest extends TestCase
{
    public function test_catalogs_expose_their_products(): void
    {
        $this->assertInstanceOf(Product::class, (new Brand)->products()->getRelated());
        $this->assertInstanceOf(Product::class, (new Category)->products()->getRelated());
        $this->assertSame('products.brand_id', (new Brand)->products()->getQualifiedForeignKeyName());
        $this->assertSame('products.category_id', (new Category)->products()->getQualifiedForeignKeyName());
    }

    public function test_catalog_active_flags_are_booleans(): void
    {
        $this->assertSame('boolean', (new Brand)->getCasts()['is_active']);
        $this->assertSame('boolean', (new Category)->getCasts()['is_active']);
        $this->assertSame('boolean', (new Supplier)->getCasts()['is_active']);
    }

    public function test_supplier_has_many_purchases(): void
    {
        $this->assertInstanceOf(Purchase::class, (new Supplier)->purchases()->getRelated());
        $this->assertSame('purchases.supplier_id', (new Supplier)->purchases()->getQualifiedForeignKeyName());
    }

    public function test_product_casts_prices_stock_and_active_flag(): void
    {
        $casts = (new Product)->getCasts();

        $this->assertSame('decimal:2', $casts['purchase_price']);
        $this->assertSame('decimal:2', $casts['sale_price']);
        $this->assertSame('integer', $casts['stock_quantity']);
        $this->assertSame('integer', $casts['min_stock']);
        $this->assertSame('integer', $casts['max_stock']);
        $this->assertSame('boolean', $casts['is_active']);
    }

    public function test_product_relations(): void
    {
        $product = new Product;

        $this->assertInstanceOf(Category::class, $product->category()->getRelated());
        $this->assertInstanceOf(Brand::class, $product->brand()->getRelated());
        $this->assertInstanceOf(PurchaseItem::class, $product->purchaseItems()->getRelated());
        $this->assertInstanceOf(StockMovement::class, $product->stockMovements()->getRelated());
    }

    public function test_purchase_casts_amounts_and_date(): void
    {
        $casts = (new Purchase)->getCasts();

        $this->assertSame('date', $casts['purchase_date']);
        $this->assertSame('decimal:2', $casts['subtotal']);
        $this->assertSame('decimal:2', $casts['tax']);
        $this->assertSame('decimal:2', $casts['total']);
    }

    public function test_purchase_relations(): void
    {
        $purchase = new Purchase;

        $this->assertInstanceOf(Supplier::class, $purchase->supplier()->getRelated());
        $this->assertInstanceOf(PurchaseItem::class, $purchase->items()->getRelated());
        $this->assertSame('purchase_items.purchase_id', $purchase->items()->getQualifiedForeignKeyName());
    }

    public function test_purchase_item_casts_and_relations(): void
    {
        $item = new PurchaseItem;
        $casts = $item->getCasts();

        $this->assertSame('integer', $casts['quantity']);
        $this->assertSame('decimal:2', $casts['unit_price']);
        $this->assertSame('decimal:2', $casts['total']);
        $this->assertInstanceOf(Purchase::class, $item->purchase()->getRelated());
        $this->assertInstanceOf(Product::class, $item->product()->getRelated());
    }

    public function test_stock_movement_casts_quantities_as_integers(): void
    {
        $casts = (new StockMovement)->getCasts();

        $this->assertSame('integer', $casts['quantity']);
        $this->assertSame('integer', $casts['previous_stock']);
        $this->assertSame('integer', $casts['new_stock']);
    }

    public function test_stock_movement_can_be_traced_to_its_origin(): void
    {
        $movement = new StockMovement;

        $this->assertInstanceOf(Product::class, $movement->product()->getRelated());
        $this->assertInstanceOf(Purchase::class, $movement->purchase()->getRelated());
        $this->assertInstanceOf(ServiceOrder::class, $movement->serviceOrder()->getRelated());
    }
}
