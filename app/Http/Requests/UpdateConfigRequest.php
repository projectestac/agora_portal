<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigRequest extends FormRequest {
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
            'notify_address_quota' => 'string|nullable',
            'notify_address_request' => 'string|nullable',
            'notify_address_user_cco' => 'string|nullable',
            'quota_usage_to_request' => 'numeric|nullable',
            'quota_free_to_request' => 'integer',
            'quota_usage_to_notify' => 'numeric',
            'quota_free_to_notify' => 'integer',
            'xtecadmin_hash' => 'string|nullable',
            'max_file_size_for_large_upload' => 'integer',
            'nodes_create_db' => 'in:on,off',
            'min_db_ib' => 'integer',
            'google_client_id' => 'string|nullable',
            'google_client_secret' => 'string|nullable',
            'google_redirect_uri' => 'string|nullable',
        ];
    }
}
