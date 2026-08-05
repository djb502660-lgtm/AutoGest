<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceRequest extends FormRequest
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
        return [
            'service_order_id' => ['nullable', 'exists:service_orders,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'mechanic_id' => ['required', 'exists:users,id'],
            'type' => ['required', Rule::in(['preventivo', 'correctivo', 'garantia'])],
            'description' => ['required', 'string', 'max:255'],
            'mileage_at_service' => ['required', 'integer', 'min:0'],
            'fuel_level' => ['required', 'string', 'in:Reserva,1/4,1/2,3/4,Lleno'],
            'inventory_spare_wheel' => ['nullable', 'boolean'],
            'inventory_tools' => ['nullable', 'boolean'],
            'inventory_radio' => ['nullable', 'boolean'],
            'inventory_documents' => ['nullable', 'boolean'],
            'parts_used' => ['nullable', 'string'],
            'technical_notes' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'parts_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pendiente', 'en_proceso', 'completado', 'cancelado'])],
            'performed_at' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_order_id' => 'orden de servicio',
            'vehicle_id' => 'vehículo',
            'mechanic_id' => 'mecánico',
            'type' => 'tipo',
            'description' => 'descripción',
            'mileage_at_service' => 'kilometraje al servicio',
            'fuel_level' => 'nivel de combustible',
            'inventory_spare_wheel' => 'rueda de repuesto',
            'inventory_tools' => 'herramientas',
            'inventory_radio' => 'radio',
            'inventory_documents' => 'documentos',
            'parts_used' => 'piezas usadas',
            'technical_notes' => 'notas técnicas',
            'cost' => 'costo',
            'parts_cost' => 'costo de piezas',
            'labor_cost' => 'costo de mano de obra',
            'status' => 'estado',
            'performed_at' => 'fecha realizada',
        ];
    }
}
