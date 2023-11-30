<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestTypeRequest extends FormRequest {
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
            'name' => [
                'required',
                'string',
                ':max:255',
            ],
            'description' => [
                'required',
                'string',
                ':max:2000',
            ],
            'prompt' => [
                'required',
                'string',
                ':max:2000',
            ],
        ];
    }
}
