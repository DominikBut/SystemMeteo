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
            'temp_air'       => 'sometimes|nullable|numeric|min:-100|max:100',
            'humidity'       => 'sometimes|nullable|numeric|min:0|max:100',
            'wind_speed'     => 'sometimes|nullable|numeric|min:0|max:1000',
            'wind_direction' => 'sometimes|nullable|integer|min:0|max:360',
            'rain_10min'     => 'sometimes|nullable|numeric|min:0|max:10000',
        ];
    }
}
