<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingLogRequest extends FormRequest
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
            'id' => ['nullable', 'exists:training_logs,id'],
            'training_session_id' => ['nullable', 'exists:training_sessions,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'numeric', 'min:0'],
            'avg_speed' => ['nullable', 'numeric', 'min:0'],
            'avg_heart_rate' => ['nullable', 'integer', 'min:0'],
            'elevation_m' => ['nullable', 'numeric', 'min:0'],
            'temperature_c' => ['nullable', 'numeric'],
            'weight' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'rpm' => ['nullable', 'numeric', 'min:0'],
            'intensity' => ['nullable', 'in:low,medium,high,very_high'],
            'type' => ['nullable', 'string', 'max:255'],
            'athlete_notes' => ['nullable', 'string', 'max:2000'],
            'completion_status' => ['nullable', 'in:not_started,in_progress,completed,incomplete'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx'],
        ];
    }
}
