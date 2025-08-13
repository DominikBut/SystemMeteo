<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'station_id'     => 'required|string|exists:stations,id',
            'temp_air'       => 'required|numeric|min:-100|max:100',
            'humidity'       => 'required|numeric|min:0|max:100',
            'wind_speed'     => 'required|numeric|min:0|max:1000',
            'wind_direction' => 'required|integer|min:0|max:360',
            'rain_10min'     => 'required|numeric|min:0|max:10000',
        ];
    }
}
