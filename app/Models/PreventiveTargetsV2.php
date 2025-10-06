<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveTargetsV2 extends Model
{
    /** @use HasFactory<\Database\Factories\PreventiveTargetsV2Factory> */
    use HasFactory;

    protected $guarded = ['id'];

    public function equipmentType()
    {
        // Hubungkan foreign key 'equipment_type_id' ke 'MasterEquipmentType'
        return $this->belongsTo(MasterEquipmentType::class, 'equipment_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
