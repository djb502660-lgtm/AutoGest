<?php

namespace App\Http\Requests\Admin;

use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ServiceOrder::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:mantenimientos,gastos,vehiculos,pendientes,inventario,productos,movimientos,categorias,vehiculo_detalle,vehiculo_general'],
            'scope' => ['nullable', 'in:vehicle,all'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'client_id' => ['nullable', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'string'],
            'mechanic_id' => ['nullable', 'exists:users,id'],
            'maintenance_type' => ['nullable', 'in:preventivo,correctivo,garantia'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'stock_status' => ['nullable', 'in:all,low,out'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required_if' => 'Debe seleccionar un vehículo cuando el alcance es específico.',
            'vehicle_id.exists' => 'El vehículo seleccionado no existe.',
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'mechanic_id.exists' => 'El mecánico seleccionado no existe.',
            'to.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
        ];
    }
}
