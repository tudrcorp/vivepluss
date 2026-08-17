<?php

namespace App\Mail;

use App\Models\Affiliation;
use App\Models\PaidMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliationAutoActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string|null  $logoPath  Ruta absoluta local del logo (se embebe vía cid:, no una URL pública).
     * @param  array<int, string>  $documentPaths  Rutas absolutas del/los comprobante(s) de pago cargado(s) por el analista.
     * @param  string|null  $referenceZelle  Referencia de Zelle capturada en el formulario (no queda persistida en paid_memberships).
     */
    public function __construct(
        public Affiliation $affiliation,
        public string $companyName,
        public ?string $logoPath = null,
        public array $documentPaths = [],
        public ?PaidMembership $paidMembership = null,
        public ?string $referenceZelle = null,
        public string $primaryColor = '#A13DDB',
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: "Activación automática de afiliación {$this->affiliation->code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.afiliacion-activada-automaticamente',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, string>
     */
    public function attachments(): array
    {
        return $this->documentPaths;
    }
}
