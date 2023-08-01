<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest {
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
            'user_comment' => 'nullable|string',
            'admin_comment' => 'nullable|string',
            'private_note' => 'nullable|string',
            'request_select_request' => 'regex:/^\d+:\d+$/',
        ];
    }
}
