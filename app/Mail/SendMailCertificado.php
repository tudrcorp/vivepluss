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

class SendMailCertificado extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $certificadoPath  Ruta absoluta del certificado en disco.
     * @param  int|string|null  $whiteCompanyId  Marca blanca dueña de la afiliación, para el logo/color del correo.
     */
    public function __construct(
        public string $certificadoPath,
        public int|string|null $whiteCompanyId = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: 'Certificado de Afiliación',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.certificado',
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
            $this->certificadoPath,
        ];
    }
}
