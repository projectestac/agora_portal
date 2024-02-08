<?php

namespace App\Http\Requests;

use App\Helpers\Access;
use App\Models\Instance;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstanceRequest extends FormRequest {
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
            'status' => 'required|string|in:' . Instance::STATUS_PENDING . ',' . Instance::STATUS_ACTIVE . ',' .
                Instance::STATUS_INACTIVE . ',' . Instance::STATUS_DENIED . ',' . Instance::STATUS_WITHDRAWN . ',' . Instance::STATUS_BLOCKED,
            'model_type_id' => 'required|numeric',
            'db_host' => 'nullable|string',
            'send_email' => 'nullable|in:on',
            'quota' => 'required|numeric|min:1',
            'observations' => 'nullable|string',
            'annotations' => 'nullable|string',
        ];
    }
}
