<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmCheckRound extends Model
{
    /** @use HasFactory<\Database\Factories\PmCheckRoundFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
