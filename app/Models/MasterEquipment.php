<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterEquipment extends Model
{
    /** @use HasFactory<\Database\Factories\MasterEquipmentFactory> */
    use HasFactory;

    protected $guarded =[] ;

    public function room()
    {
        return $this->belongsTo(MasterRoom::class, 'room_id');
    }

    public function type()
    {
        return $this->belongsTo(MasterEquipmentType::class, 'equipment_type_id');
    }

    public function preventiveTasks()
    {
        return $this->hasMany(PreventiveTask::class, 'equipment_id');
    }
}
