<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreInstanceRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Access::isAdmin($this->user()) || Access::isManager($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array {
        return [
            'client_id' => 'integer|required',
            'service_id' => 'integer|required',
            'model_type_id' => 'integer|required',
            'contact_profile' => 'string|required',
        ];
    }

    public function messages(): array {
        return [
            'client_id.required' => __('instance.client_id_required'),
            'service_id.required' => __('instance.service_id_required'),
            'model_type_id.required' => __('instance.model_type_id_required'),
            'contact_profile.string' => __('instance.contact_profile_required'),
            'contact_profile.required' => __('instance.contact_profile_required'),
        ];
    }
}
