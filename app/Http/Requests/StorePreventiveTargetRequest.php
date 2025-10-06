<?php

namespace App\Http\Requests;

use App\Models\MasterEquipment;
use App\Models\MasterEquipmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StorePreventiveTargetRequest extends FormRequest
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
        // 1. Hitung jumlah unit yang tersedia untuk tipe yang dipilih
        $equipmentTypeId = $this->input('equipment_type_id');
        $unitCount = MasterEquipment::where('equipment_type_id', $equipmentTypeId)->count();
        
        return [
            'equipment_type_id' => [
                'required', 
                'exists:master_equipment_types,id',
                // Perhatian: Pastikan nama tabel ini benar (preventive_targets_v2)
                Rule::unique('preventive_targets_v2_s')->where(function ($query) {
                    return $query->where('month', $this->month)
                                ->where('year', $this->year);
                })->ignore($this->route('target')), // <--- KRUSIAL!
            ],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:' . (date('Y') - 2)],
            
            'target_count' => [
                'required', 
                'integer', 
                'min:1',
                // 2. Tambahkan rule 'max' dinamis
                'max:' . $unitCount, 
            ],
        ];
    }
    
    // Tambahkan pesan kustom untuk rule 'max'
    public function messages(): array
    {
        // Hitung ulang untuk pesan kustom
        $equipmentTypeId = $this->input('equipment_type_id');
        $unitCount = MasterEquipment::where('equipment_type_id', $equipmentTypeId)->count();
        
        // Ambil nama equipment untuk pesan yang lebih jelas
        $equipmentType = MasterEquipmentType::find($equipmentTypeId)->name ?? 'Tipe Equipment ini';
        
        return [
            'equipment_type_id.unique' => 'Target untuk tipe equipment ini pada bulan dan tahun yang dipilih sudah pernah dibuat.',
            
            // Pesan khusus untuk melebihi batas unit
            'target_count.max' => "Target Preventive untuk {$equipmentType} melebihi jumlah unit yang tersedia. Hanya ada {$unitCount} unit yang terdaftar.",
            
            // ... (Pesan validasi lain) ...
        ];
    }

}
