<?php

namespace App\Http\Requests;

use App\Models\plnMeterReading;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreplnMeterReadingRequest extends FormRequest
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
            'id_pelanggan_pln' => [
                'required',
                Rule::unique('pln_meter_readings')
                    ->where(fn ($q) =>
                        $q->where('tanggal_pencatatan', $this->tanggal_pencatatan)
                    ),
            ],
            'tanggal_pencatatan' => 'required|date',
            'jam_pencatatan'     => 'required',
            'cos_phi'            => 'nullable|numeric',
            'wbp'                => 'nullable|numeric',
            'lwbp'               => 'nullable|numeric',
            'kwh'                => 'nullable|numeric',
            'kvarh'              => 'nullable|numeric',
            'temuan'             => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $last = plnMeterReading::where('id_pelanggan_pln', request('id_pelanggan_pln'))
                ->orderBy('tanggal_pencatatan', 'desc')
                ->first();

            if ($last && request('kwh') < $last->kwh) {
                $validator->errors()->add(
                    'kwh',
                    'Nilai kWh tidak boleh lebih kecil dari pencatatan sebelumnya ('.$last->kwh.')'
                );
            }
        });
    }

}
