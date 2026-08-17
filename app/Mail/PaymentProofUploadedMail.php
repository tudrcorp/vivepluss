<?php

namespace App\Mail;

use App\Http\Controllers\AffiliationController;
use App\Models\Affiliation;
use App\Models\PaidMembership;
use App\Support\Mail\MailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso a los contactos que Integracorp le indicó a ViVEplus (configurados
 * por marca blanca en Configuration::payment_notification_emails) de que un
 * analista cargó un comprobante de pago. Se dispara en cada carga, para
 * cualquier método de pago -ver AffiliationController::notifyPaymentProofUploaded().
 */
class PaymentProofUploadedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public bool $isCredito;

    public function __construct(
        public Affiliation $affiliation,
        public PaidMembership $paidMembership,
        public string $companyName,
    ) {
        $this->isCredito = $paidMembership->payment_method === 'CREDITO';
    }

    public function envelope(): Envelope
    {
        $label = $this->isCredito ? 'Nota de crédito generada' : 'Comprobante de pago cargado';

        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: "{$label}: {$this->affiliation->code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.comprobante-cargado',
            with: [
                'isCredito' => $this->isCredito,
                ...MailBranding::forWhiteCompany($this->affiliation->white_company_id),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return collect(AffiliationController::resolvePaymentDocumentPaths($this->paidMembership))
            ->map(fn (string $path) => Attachment::fromPath($path))
            ->all();
    }
}
