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

    public function generateMessage(): string
    {
        $pending = "";
        $name = auth()->user()->name;
        $header = "";

        switch ($this->status) {
            case 'open':
                $header = "*[{$this->status}]*";
                break;

            case 'in_progress':
                if ($this->assigned_employee_id != auth()->id()) {
                    $header = "*[Delegated by : {$this->dept->head->name}]*\nTo\n*[Processed by : {$this->assigned->name}]*";
                } else {
                    $header = "*[Processed by : {$this->assigned->name}]*";
                }
                break;

            case 'pending':
                $header = "*[Pending by : {$this->assigned->name}]*";
                $pending = "*[ Alasan Pending ]* \n{$this->pending_reason}";
                break;

            case 'escalated':
                $header = "*[ Escalated from : {$name} ]* \n*[ Escalated to : {$this->assigned->name} ]*";
                break;

            case 'solved':
                $header = "*[Solved by : {$this->assigned->name}]*";
                break;
        }

        $message =
<<<MSG
$header
*Title : {$this->title}*

From        : {$this->requester->name}
Department  : {$this->dept->name}
Message     : {$this->description}

$pending

_http://127.0.0.1:8000/ticketing/{$this->id}/show_
_https://thinksys.my.id/ticketing/{$this->id}/show_
MSG;

        return $message;
    }




}
