<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'u_id'     => 'required|numeric',
            'app_id'   => 'required|numeric',
            'language' => 'required|min:2|max:10',
            'os'       => 'required|min:2|max:30',
        ];
    }

}
