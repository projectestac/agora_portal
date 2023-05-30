<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreManagerRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Access::isAdmin($this->user()) ||
            Access::isClient($this->user()) ||
            Access::isManager($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array {
        return [
            'username' => [
                'required',
                'string',
                'max:17',
                'regex:/^(?:[a-zA-Z0-9]{1,8}|[a-zA-Z0-9]{1,8}@xtec\.cat)$/',
            ],
        ];
    }

    public function messages(): array {
        return [
            'username.required' => __('manager.username_required'),
            'username.string' => __('manager.username_string'),
            'username.max' => __('manager.username_max'),
            'username.regex' => __('manager.username_regex'),
        ];
    }
}
