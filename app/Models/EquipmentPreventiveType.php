<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentPreventiveType extends Model
{
    /** @use HasFactory<\Database\Factories\EquipmentPreventiveTypeFactory> */
    use HasFactory;

    protected $guarded = [];

    public function equipmentTypes()
    {
        return $this->belongsToMany(MasterEquipmentType::class, 'equipment_preventive_type');
    }

    public function taskDetails()
    {
        return $this->hasMany(PreventiveTaskDetail::class, 'preventive_type_id');
    }

    public function equipmentPreventive()
    {
        return $this->belongsTo(MasterPreventive::class, 'preventive_type_id');
    }

}
