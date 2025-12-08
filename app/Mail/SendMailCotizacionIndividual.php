<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class SendMailCotizacionIndividual extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $cotizacion;

    /**
     * Create a new message instance.
     */
    public function __construct($cotizacion)
    {
        $this->cotizacion = $cotizacion;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: 'Cotización Individual',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.cotizacion-individual',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            public_path('storage/quotes/' . $this->cotizacion),
            // $this->attachFromStorage('public/ejemploCSV.csv', 'ejemploCSV.csv'),
        ];
    }
}