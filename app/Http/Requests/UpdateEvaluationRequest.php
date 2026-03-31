<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationRequest extends FormRequest
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
            'attendance_status' => ['sometimes', 'in:present,absent,late,excused'],
            'completion_status' => ['sometimes', 'in:not_started,in_progress,completed,incomplete'],
            'coach_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'coach_evaluation' => ['nullable', 'string', 'max:5000'],
            'coach_comments' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
