<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dept()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function pks()
    {
        return $this->hasMany(Pks::class);
    }

    public function technicianSpecialists()
    {
        return $this->hasMany(TechnicianSpecialist::class, 'user_id');
    }

    public function departmentHeaded()
    {
        return $this->hasOne(Department::class, 'head_id');
    }

    public function specializations()
    {
        return $this->belongsToMany(
            Specializations::class, // nama model spesialisasi
            'technician_specialists',           // nama tabel pivot
            'user_id',                          // foreign key di pivot untuk user
            'specialization_id'                 // foreign key di pivot untuk specialization
        );
    }


}
