<?php

namespace App\Mail;

use App\Models\Reserva; // Importe seu model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment; // Para anexos
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf; // Importe o DOMPDF

class VoucherReservaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;

    /**
     * Create a new message instance.
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu Voucher de Reserva - Hotel Estação Chico', // Assunto do email
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Este é o template Blade para o CORPO do email
        return new Content(
            view: 'emails.voucher', 
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // 1. Carregar os dados necessários (igual você fez no controller)
        $this->reserva->load('hospede', 'quarto.categoria', 'transacoes.formaPagamento');

        $dataCheckin = \Carbon\Carbon::parse($this->reserva->data_checkin);
        $dataCheckout = \Carbon\Carbon::parse($this->reserva->data_checkout);
        $numDiarias = $dataCheckout->diffInDays($dataCheckin);
        $valorTotal = $this->reserva->valor_diaria * $numDiarias;

        $preferencias = \App\Models\PreferenciasHotel::first();
        
        $data = [
            'reserva' => $this->reserva,
            'numeroVoucher' => $this->reserva->id,
            'dataEmissao' => now()->format('d/m/Y'),
            'dataCheckin' => $dataCheckin->format('d/m/Y'),
            'horaCheckin' => $preferencias->hora_checkin ?? '14:00',
            'dataCheckout' => $dataCheckout->format('d/m/Y'),
            'horaCheckout' => $preferencias->hora_checkout ?? '12:00',
            'numDiarias' => $numDiarias,
            'valorDiaria' => $this->reserva->valor_diaria,
            'valorTotal' => $valorTotal,
        ];

        // 2. Gerar o PDF em memória (usando seu template.blade.php)
        $pdf = Pdf::loadView('vouchers.template', $data); // Certifique-se que o nome 'template' está correto

        // 3. Anexar o PDF
        return [
            Attachment::fromData(fn () => $pdf->output(), 'Voucher-Reserva-' . $this->reserva->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}