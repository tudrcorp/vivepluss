<?php

namespace App\Mail;

use App\Support\Mail\MailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMailCotizacionCorporativa extends Mailable
{
    use Queueable, SerializesModels;

    public $cotizacion;

    /**
     * @param  int|string|null  $whiteCompanyId  Marca blanca dueña de la cotización, para el logo/color del correo.
     */
    public function __construct($cotizacion, public int|string|null $whiteCompanyId = null)
    {
        $this->cotizacion = $cotizacion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: 'Propuesta Corporativa VivePlus: Soluciones de salud para su organización 🏢',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.cotizacion-corporativa',
            with: MailBranding::forWhiteCompany($this->whiteCompanyId),
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            public_path('storage/quotes/'.$this->cotizacion),
        ];
    }
}
