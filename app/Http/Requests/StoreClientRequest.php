<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest {
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
            'code' => [
                'required',
                'string',
                'regex:/^([abce])\d{7}$/',
            ],
            'dns' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-z0-9-_]+$/',
            ],
            'old_dns' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[a-z0-9-_]+$/',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'location' => [
                'required',
                'exists:locations,id',
            ],
            'client_type' => [
                'required',
                'exists:client_types,id',
            ],
            'visible' => [
                'required',
                'in:yes,no',
            ],
        ];
    }
}
