<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmShiftTask extends Model
{
    /** @use HasFactory<\Database\Factories\PmShiftTaskFactory> */
    use HasFactory;

    protected $guarded = ['id'];
    // Relasi ke MasterPmTask
    public function masterPmTask()
    {
        return $this->belongsTo(MasterPmTask::class, 'master_pm_task_id');
    }
    
    // Relasi ke User yang menyelesaikan tugas
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
