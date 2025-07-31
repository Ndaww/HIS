<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveTask extends Model
{
    /** @use HasFactory<\Database\Factories\PreventiveTaskFactory> */
    use HasFactory;

    protected $guarded = [];

    public function equipment()
    {
        return $this->belongsTo(MasterEquipment::class, 'equipment_id');
    }

    public function room()
    {
        return $this->belongsTo(MasterRoom::class, 'room_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function details()
    {
        return $this->hasMany(PreventiveTaskDetail::class, 'task_id');
    }
}
