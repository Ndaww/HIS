<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $guarded = [];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assigned()
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    public function dept()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function attachmentsOpen()
    {
        return $this->hasMany(TicketAttachment::class)->where('type', 'open');
    }

    public function attachmentsClose()
    {
        return $this->hasMany(TicketAttachment::class)->where('type', 'close');
    }



}
