<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterRoom extends Model
{
    /** @use HasFactory<\Database\Factories\MasterRoomFactory> */
    use HasFactory;

    protected $guarded = [];

    public function equipments()
    {
        return $this->hasMany(MasterEquipment::class, 'room_id');
    }

    public function preventiveTasks()
    {
        return $this->hasMany(PreventiveTask::class, 'room_id');
    }
}
