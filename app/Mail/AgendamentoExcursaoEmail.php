<?php

namespace App\Mail;

use App\Models\Excursao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgendamentoExcursaoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Excursao $excursao) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informações do agendamento da excursão #'.$this->excursao->id,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.agendamento_excursao');
    }

    public function attachments(): array
    {
        return [];
    }
}
