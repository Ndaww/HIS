<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pks extends Model
{
    /** @use HasFactory<\Database\Factories\PksFactory> */
    use HasFactory;

    protected $guarded = [];

    public function requester()
    {
        return $this->belongsTo(User::class);
    }

}
