<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceOrderRequest extends FormRequest
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
        $order = $this->route('order');
        $isUpdate = $order !== null;

        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'mechanic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', UserRole::Mechanic->value)->where('status', 'activo')),
            ],
            'priority' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            'description' => ['required', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => [$isUpdate ? 'required' : 'nullable', Rule::in(['recibida', 'en_proceso', 'completada', 'entregada', 'cancelada'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'vehicle_id' => 'vehículo',
            'mechanic_id' => 'mecánico',
            'priority' => 'prioridad',
            'description' => 'descripción',
            'scheduled_at' => 'fecha programada',
            'estimated_cost' => 'costo estimado',
            'status' => 'estado',
        ];
    }
}
