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
        \Log::info($this->ticket->id.'telenotif');
        try {
            if($this->ticket->status == 'open'){
                $header  = "<b>[{$this->ticket->status}]</b>";
            }
            if($this->ticket->status == 'in_progress'){
                $header  = "<b>[Processed by : {$this->ticket->assigned->name}]</b>";
                $header  .= "\n<b>[Date : {$this->ticket->updated_at}]</b>";
            }

            $message = <<<MSG
                        $header
                        <b>Title : {$this->ticket->title}</b>

                        <b>From:</b> {$this->ticket->requester->name}
                        <b>Department:</b> {$this->ticket->dept->name}
                        <b>Message:</b> {$this->ticket->description}

                        <a href="http://127.0.0.1:8000/ticketing/{$this->ticket->id}/show"> http://127.0.0.1:8000/ticketing/{$this->ticket->id}/show </a>
                        MSG;
             

            return TelegramMessage::create()
                ->to(env('TELEGRAM_CHAT_ID')) // atau langsung chat_id
                ->content($message)
                ->options(['parse_mode' => 'HTML']);



        } catch (\Throwable $e) {
            \Log::info('Ticket asd', ['ticket' => $this->ticket]);

            throw $e;
        }
    }
}
