<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masterpmtask extends Model
{
    /** @use HasFactory<\Database\Factories\MasterpmtaskFactory> */
    use HasFactory;

    protected $guarded =['id'];

    public function equipmentType()
    {
        return $this->belongsTo(MasterEquipmentType::class, 'equipment_type_id');
    }

}
