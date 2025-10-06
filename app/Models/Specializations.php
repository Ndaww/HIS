<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specializations extends Model
{
    /** @use HasFactory<\Database\Factories\SpecializationsFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public function technicianSpecialists()
    {
        return $this->hasMany(TechnicianSpecialist::class, 'specialization_id');
    }

    public function type()
    {
        return $this->belongsTo(MasterEquipmentType::class);
    }

}
