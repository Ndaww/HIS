<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterEquipmentType extends Model
{
    /** @use HasFactory<\Database\Factories\MasterEquipmentTypeFactory> */
    use HasFactory;

    protected $guarded = [];

    public function equipments()
    {
        return $this->hasMany(MasterEquipment::class, 'equipment_type_id');
    }

    public function preventiveTypes()
    {
        return $this->belongsToMany(EquipmentPreventiveType::class, 'equipment_preventive_type');
    }

}
