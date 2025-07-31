<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveTaskDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PreventiveTaskDetailFactory> */
    use HasFactory;

    protected $guarded =[];

    public function task()
    {
        return $this->belongsTo(PreventiveTask::class, 'task_id');
    }

    public function preventiveType()
    {
        return $this->belongsTo(EquipmentPreventiveType::class, 'preventive_type_id');
    }

}
