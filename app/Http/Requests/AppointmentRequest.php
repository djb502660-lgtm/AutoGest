<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
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
            'mechanic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', UserRole::Mechanic->value)->where('status', 'activo')),
            ],
            'advisor_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mechanic_id' => 'mecánico',
            'advisor_notes' => 'notas del asesor',
        ];
    }
}
