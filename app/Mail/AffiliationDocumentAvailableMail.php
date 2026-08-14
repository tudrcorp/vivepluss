<?php

namespace App\Mail;

use App\Models\AffiliationDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliationDocumentAvailableMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $documentTypeLabel;

    public ?string $affiliateName;

    public function __construct(
        public AffiliationDocument $document,
        public string $companyName,
    ) {
        $this->documentTypeLabel = $document->document_type === AffiliationDocument::TYPE_CARNET ? 'Carnet' : 'Certificado';

        $this->affiliateName = match ($document->affiliation_kind) {
            AffiliationDocument::KIND_INDIVIDUAL => $document->affiliate?->full_name,
            AffiliationDocument::KIND_CORPORATE => trim(($document->affiliateCorporate?->first_name ?? '').' '.($document->affiliateCorporate?->last_name ?? '')) ?: null,
            default => null,
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: "{$this->documentTypeLabel} disponible: {$this->document->affiliation_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.documento-disponible',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->document->existsOnDisk()) {
            return [];
        }

        return [Attachment::fromPath($this->document->absolutePath())];
    }
}
