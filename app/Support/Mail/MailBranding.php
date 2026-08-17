<?php

namespace App\Support\Mail;

use App\Models\Configuration;
use Illuminate\Support\Facades\Storage;

/**
 * Resuelve el logo y el color primario de la marca blanca dueña de un
 * white_company_id, para que cualquier Mailable arme su correo con
 * <x-mail.layout> sin repetir esta búsqueda (antes vivía duplicada entre
 * AffiliationController::resolveLogoPath y WelcomeKitMail).
 */
class MailBranding
{
    /**
     * @return array{logoPath: ?string, primaryColor: string}
     */
    public static function forWhiteCompany(int|string|null $whiteCompanyId): array
    {
        $configuration = Configuration::where('white_company_id', $whiteCompanyId)->first()
            ?? Configuration::query()->first();

        return [
            'logoPath' => static::resolveLogoPath($configuration),
            'primaryColor' => $configuration?->primaryColor ?: '#A13DDB',
        ];
    }

    /**
     * brandLogo se sube sin disco explícito, así que puede haber terminado
     * en 'public' o en 'local' según el ambiente; se prueban ambos.
     */
    private static function resolveLogoPath(?Configuration $configuration): ?string
    {
        if (blank($configuration?->brandLogo)) {
            return null;
        }

        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($configuration->brandLogo)) {
                return Storage::disk($disk)->path($configuration->brandLogo);
            }
        }

        return null;
    }
}
