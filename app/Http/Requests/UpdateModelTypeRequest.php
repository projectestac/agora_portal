<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModelTypeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Access::isAdmin($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array {
        return [
            'description' => [
                'required',
                'string',
                ':max:255',
            ],
            'short_code' => [
                'required',
                'string',
                ':max:255',
            ],
            'url' => [
                'nullable',
                'url',
                ':max:255',
            ],
            'db' => [
                'nullable',
                'string',
                ':max:255',
            ],
        ];
    }

    public function messages(): array {
        return [
            'description.required' => __('modeltype.description_required'),
            'description.string' => __('modeltype.description_string'),
            'description.max' => __('modeltype.description_max'),
            'short_code.required' => __('modeltype.short_code_required'),
            'short_code.string' => __('modeltype.short_code_string'),
            'short_code.max' => __('modeltype.short_code_max'),
            'url.url' => __('modeltype.url_url'),
            'url.max' => __('modeltype.url_max'),
            'db.string' => __('modeltype.db_string'),
            'db.max' => __('modeltype.db_max'),
        ];
    }
}
