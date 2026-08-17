<?php

namespace App\Mail;

use App\Models\Affiliation;
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
 * Implementa ShouldQueue para que Mail::send() no bloquee la request del
 * analista mientras arma los adjuntos y habla con el SMTP -se encola igual
 * que SendAffiliationDocumentWhatsApp, sin que la acción tenga que llamar
 * a queue() explícitamente.
 */
class WelcomeKitMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $files  Nombre a mostrar => ruta absoluta en disco.
     */
    public function __construct(
        public Affiliation $affiliation,
        public array $files,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: 'Kit de Bienvenida - '.$this->affiliation->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.kit-bienvenida',
            with: [
                'affiliation' => $this->affiliation,
                'firstName' => filled($this->affiliation->full_name_ti) ? strtok($this->affiliation->full_name_ti, ' ') : null,
                ...MailBranding::forWhiteCompany($this->affiliation->white_company_id),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return collect($this->files)
            ->map(fn (string $path, string $name) => Attachment::fromPath($path)->as($name))
            ->values()
            ->all();
    }
}
