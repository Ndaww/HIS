<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramTicketNotification extends Notification
{
    protected $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        // \Log::info($this->ticket->pending_reason.$this->ticket->status.'telenotif');
        try {
            $pending = "";
            $name = auth()->user()->name;
            if($this->ticket->status == 'open'){
                $header  = "<b>[{$this->ticket->status}]</b>";
            }
            if($this->ticket->status == 'in_progress' ){
                if($this->ticket->assigned_employee_id != auth()->user()->id ){
                    $header  = "<b>[Delegated by : {$this->ticket->dept->head->name}]</b>\nTo\n<b>[Processed by : {$this->ticket->assigned->name}]</b>";
                } else {
                    $header  = "<b>[Processed by : {$this->ticket->assigned->name}]</b>";
                }
            }
            if($this->ticket->status == 'pending'){
                $header  = "<b>[Pending by : {$this->ticket->assigned->name}]</b>";
                $pending = "<b>[ Alasan Pending ] \n{$this->ticket->pending_reason} </b>";
            }
            if($this->ticket->status == 'escalated'){
                $header  = "<b>[ Escalated from : {$name} ] \n[ Escalated to : {$this->ticket->assigned->name} ]</b>";
            }
            if($this->ticket->status == 'solved'){
                $header  = "<b>[Solved by : {$this->ticket->assigned->name}]</b>";
            }
            

            $message = <<<MSG
                        $header
                        <b>Title : {$this->ticket->title}</b>

                        <b>From:</b> {$this->ticket->requester->name}
                        <b>Department:</b> {$this->ticket->dept->name}
                        <b>Message:</b> {$this->ticket->description}
                        
                        $pending

                        <a href="http://127.0.0.1:8000/ticketing/{$this->ticket->id}/show"> http://127.0.0.1:8000/ticketing/{$this->ticket->id}/show </a>
                        MSG;
             

            return TelegramMessage::create()
                ->to(env('TELEGRAM_CHAT_ID'))
                ->content($message)
                ->options(['parse_mode' => 'HTML']);



        } catch (\Throwable $e) {
            \Log::info('Ticket asd', ['ticket' => $this->ticket]);
            \Log::info($pending.'Pending woi');

            throw $e;
        }
    }
}
