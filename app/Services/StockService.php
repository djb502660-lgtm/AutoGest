<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;

class StockService
{
    /** Aplica el movimiento al stock del producto y registra su trazabilidad. */
    public function applyMovement(Product $product, string $type, int $quantity, array $attributes = []): StockMovement
    {
        $previousStock = $product->stock_quantity;
        $newStock = $this->resolveNewStock($previousStock, $type, $quantity);

        $product->update(['stock_quantity' => $newStock]);

        return $product->stockMovements()->create([
            ...$attributes,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
        ]);
    }

    public function resolveNewStock(int $previousStock, string $type, int $quantity): int
    {
        return match ($type) {
            'entrada' => $previousStock + $quantity,
            'salida' => $previousStock - $quantity,
            default => $quantity,
        };
    }
}
