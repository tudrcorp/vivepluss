<?php

namespace App\Support;

use App\Jobs\SendAffiliationDocumentWhatsApp;
use App\Mail\SalesForceActivatedMail;
use Illuminate\Support\Facades\Mail;

/**
 * Aviso de activación (email + WhatsApp) que reciben un agente o una
 * agencia al ser activados en la fuerza de venta, con sus credenciales por
 * defecto. Compartido entre AgenciesTable y AgentsTable para no repetir el
 * armado del mensaje en cada una.
 */
class SalesForceActivation
{
    public static function notify(
        string $name,
        ?string $email,
        ?string $phone,
        string $roleLabel,
        int|string|null $whiteCompanyId,
    ): void {
        if (filled($email)) {
            Mail::to($email)->send(new SalesForceActivatedMail($name, $email, $roleLabel, $whiteCompanyId));
        }

        if (filled($phone)) {
            $resetUrl = route('filament.viveadmin.auth.password-reset.request');

            $body = "*Activación exitosa — VivePlus* 🎉\n\n"
                ."Hola {$name}, tu activación como *{$roleLabel}* dentro de la fuerza de venta de VivePlus se realizó con éxito.\n\n"
                ."Usuario: {$email}\n"
                .'Contraseña: 12345678'."\n\n"
                ."Puedes cambiarla cuando gustes en \"¿Ha olvidado su contraseña?\": {$resetUrl}";

            SendAffiliationDocumentWhatsApp::dispatch($phone, $body);
        }
    }
}
