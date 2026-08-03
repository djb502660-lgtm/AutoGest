<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.inventory.index', [
            'products' => Product::with(['category', 'brand'])->orderBy('name')->get(),
            'categories' => Category::with('brands')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'purchases' => Purchase::with('supplier')->latest()->take(10)->get(),
        ]);
    }
}
