<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
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
        $vehicle = $this->route('vehicle');

        return [
            'client_id' => ['required', 'exists:users,id'],
            'plate' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate')->ignore($vehicle?->id)],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'mileage' => ['required', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['activo', 'inactivo', 'en_taller'])],
            'insurance_expiry' => ['nullable', 'date'],
            'inspection_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'client_id' => 'cliente',
            'plate' => 'placa',
            'brand' => 'marca',
            'model' => 'modelo',
            'year' => 'año',
            'color' => 'color',
            'mileage' => 'kilometraje',
            'vin' => 'VIN',
            'status' => 'estado',
            'insurance_expiry' => 'vencimiento de seguro',
            'inspection_expiry' => 'vencimiento de inspección',
            'notes' => 'notas',
        ];
    }
}
