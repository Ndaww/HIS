<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    /** @use HasFactory<\Database\Factories\RoomBookingFactory> */
    use HasFactory;

    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(MasterPatient::class, 'patient_id');
    }

    public function room()
    {
        return $this->belongsTo(MasterRoom::class, 'room_id');
    }

}
