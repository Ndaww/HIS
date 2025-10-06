<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmShiftSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\PmShiftScheduleFactory> */
    use HasFactory;

    protected $guarded = ['id'];
}
