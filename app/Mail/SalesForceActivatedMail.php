<?php

namespace App\Mail;

use App\Support\Mail\MailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso de activación para un agente o una agencia dentro de la fuerza de
 * venta de VivePlus, con las credenciales por defecto del usuario que se
 * acaba de crear en `users` (email + clave "12345678") y el enlace para
 * cambiarla. $roleLabel distingue el copy entre "agencia" y "agente".
 */
class SalesForceActivatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $roleLabel,
        public int|string|null $whiteCompanyId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('vivepluss@vivepluss.com', 'VIVE PLUS'),
            subject: "¡Tu activación como {$this->roleLabel} en VivePlus fue exitosa!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.fuerza-venta-activada',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'roleLabel' => $this->roleLabel,
                'resetPasswordUrl' => route('filament.viveadmin.auth.password-reset.request'),
                ...MailBranding::forWhiteCompany($this->whiteCompanyId),
            ],
        );
    }
}
