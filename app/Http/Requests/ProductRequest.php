<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product?->id)],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'categoría',
            'brand_id' => 'marca',
            'name' => 'nombre',
            'sku' => 'SKU',
            'description' => 'descripción',
            'purchase_price' => 'precio de compra',
            'sale_price' => 'precio de venta',
            'stock_quantity' => 'cantidad en stock',
            'min_stock' => 'stock mínimo',
            'max_stock' => 'stock máximo',
            'unit' => 'unidad',
            'is_active' => 'estado activo',
        ];
    }
}
