<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectorRequest extends FormRequest {

    public function authorize(): bool {
        return false;
    }

    public function rules(): array {
        return [
            'servicesel' => [
                'required',
                'integer',
            ],
            'order' => [
                'required',
                'in:clientname,dbid,clientcode,dns',
            ],
            'search' => [
                'in:code,clientname,town,dns,dbid',
            ],
            'texttosearch' => [
                'nullable',
                'string:max:255',
            ],
        ];
    }

}
