<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $newStatus,
        public ?string $observation = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $labels = [
            'confirmado' => 'Agendamento Confirmado',
            'cancelado' => 'Agendamento Cancelado',
            'concluido' => 'Servico Concluido',
        ];

        return new Envelope(
            subject: 'GestCar - ' . ($labels[$this->newStatus] ?? 'Atualizacao do Agendamento'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-status',
        );
    }
}
