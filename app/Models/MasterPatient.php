<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPatient extends Model
{
    /** @use HasFactory<\Database\Factories\MasterPatientFactory> */
    use HasFactory;

    protected $guarded = [];
}
