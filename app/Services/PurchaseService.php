<?php

namespace App\Services;

use App\Models\Purchase;

class PurchaseService
{
    public const TAX_RATE = 0.16;

    /** Reemplaza los ítems de la compra y recalcula subtotal, impuesto y total. */
    public function syncItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->delete();

        $subtotal = 0;

        foreach ($items as $item) {
            $total = $item['quantity'] * $item['unit_price'];
            $subtotal += $total;

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $total,
            ]);
        }

        $tax = $subtotal * self::TAX_RATE;

        $purchase->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }
}
