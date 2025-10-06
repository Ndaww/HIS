<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveSchedulesV2 extends Model
{
    /** @use HasFactory<\Database\Factories\PreventiveSchedulesV2Factory> */
    use HasFactory;

    protected $guarded = ['id'];

    public function equipment()
    {
        return $this->belongsTo(MasterEquipment::class, 'equipment_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
