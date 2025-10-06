<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMFormDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PMFormDetailFactory> */
    use HasFactory;

    protected $guarded = ['id'];
}
