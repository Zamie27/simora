<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role?->name === 'Pelatih';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exercise_type_id' => ['required', 'exists:exercise_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'string'],
            'repeat_weekly' => ['boolean'],
            'athlete_ids' => ['required', 'array', 'min:1'],
            'athlete_ids.*' => ['exists:users,id'],
            'target_distance_km' => ['nullable', 'numeric', 'min:0'],
            'target_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'target_avg_speed' => ['nullable', 'numeric', 'min:0'],
            'target_rpm' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
