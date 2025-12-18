<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityTour extends Model
{
    /** @use HasFactory<\Database\Factories\FacilityTourFactory> */
    use HasFactory;

    protected $guarded = [];

    public function room()
    {
        return $this->belongsTo(MasterRoom::class, 'room_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

}
