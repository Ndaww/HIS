<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmCheckResult extends Model
{
    /** @use HasFactory<\Database\Factories\PmCheckResultFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Ronde
    public function round()
    {
        return $this->belongsTo(PmCheckRound::class, 'round_id');
    }

    // Relasi ke Equipment
    public function equipment()
    {
        return $this->belongsTo(MasterEquipment::class, 'equipment_id');
    }
    
    // Relasi ke Master Tugas (untuk detail tugas/standar)
    public function masterTask()
    {
        return $this->belongsTo(MasterPmTask::class, 'master_pm_task_id');
    }

}
