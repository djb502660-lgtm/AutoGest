<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $inventorySummary = $this->inventoryService->getInventorySummary();
        $purchases = $this->inventoryService->getRecentPurchases(10);

        return view('admin.inventory.index', [
            'products' => $inventorySummary['products'],
            'categories' => $inventorySummary['categories'],
            'brands' => $inventorySummary['brands'],
            'suppliers' => $inventorySummary['suppliers'],
            'purchases' => $purchases,
        ]);
    }
}
