<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMFormHeader extends Model
{
    /** @use HasFactory<\Database\Factories\PMFormHeaderFactory> */
    use HasFactory;
    
    protected $guarded = ['id'];

    public function equipment()
    {
        return $this->belongsTo(MasterEquipment::class, 'equipment_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function details()
    {
        return $this->hasMany(PMFormDetail::class,'form_header_id');
    }
}
