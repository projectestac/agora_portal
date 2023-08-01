<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestRequest extends FormRequest {
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
            'request_id' => 'required|integer',
            'status' => 'required|in:pending,under_study,solved,denied',
            'send_email' => 'nullable|in:on',
            'admin_comment' => 'nullable|string',
            'private_note' => 'nullable|string',
        ];
    }
}
