<?php

namespace App\Support;

use App\Models\Affiliation;
use App\Models\AffiliationDocument;
use App\Models\PlanCondicionado;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Arma el "Kit de Bienvenida" de una afiliación individual: certificado +
 * carnet de cada afiliado (ambos entregados por Integracorp vía webhook,
 * ver AffiliationDocument) + el condicionado del plan (subido por un admin
 * en CondicionadosPorPlan, uno distinto por plan). El zip se genera al vuelo
 * en cada descarga/envío -no se persiste nada nuevo- porque las tres fuentes
 * ya viven en disco de forma estable.
 */
class AffiliationWelcomeKit
{
    /**
     * La acción solo debe ofrecerse cuando los documentos que genera
     * Integracorp (certificado + todos los carnets) ya llegaron; el
     * condicionado del plan no es un requisito de visibilidad porque su
     * ausencia es responsabilidad de configuración, no de Integracorp.
     */
    public static function isReadyFor(Affiliation $affiliation): bool
    {
        $certificado = AffiliationDocument::latestFor($affiliation->code, AffiliationDocument::TYPE_CERTIFICADO);

        if (! $certificado || ! $certificado->existsOnDisk()) {
            return false;
        }

        $affiliates = $affiliation->affiliates()->get();

        if ($affiliates->isEmpty()) {
            return false;
        }

        foreach ($affiliates as $affiliate) {
            $carnet = AffiliationDocument::latestFor($affiliation->code, AffiliationDocument::TYPE_CARNET, $affiliate->nro_identificacion);

            if (! $carnet || ! $carnet->existsOnDisk()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Los documentos del kit tal cual viven en disco, sin empaquetar -para
     * email/WhatsApp, donde llegan por separado y no como un zip- junto con
     * los avisos de lo que falte.
     *
     * @return array{files: array<string, string>, warnings: array<int, string>}
     */
    public static function collect(Affiliation $affiliation): array
    {
        $warnings = [];
        $files = [];

        $certificado = AffiliationDocument::latestFor($affiliation->code, AffiliationDocument::TYPE_CERTIFICADO);

        if ($certificado && $certificado->existsOnDisk()) {
            $files['Certificado de Afiliacion.pdf'] = $certificado->absolutePath();
        } else {
            $warnings[] = 'El certificado de afiliación aún no ha sido entregado por Integracorp.';
        }

        foreach ($affiliation->affiliates()->get() as $affiliate) {
            $carnet = AffiliationDocument::latestFor($affiliation->code, AffiliationDocument::TYPE_CARNET, $affiliate->nro_identificacion);

            if ($carnet && $carnet->existsOnDisk()) {
                $files["Carnet - {$affiliate->full_name}.pdf"] = $carnet->absolutePath();
            } else {
                $warnings[] = "El carnet de {$affiliate->full_name} aún no ha sido entregado por Integracorp.";
            }
        }

        $condicionado = $affiliation->plan_id ? PlanCondicionado::where('plan_id', $affiliation->plan_id)->first() : null;

        if ($condicionado && $condicionado->existsOnDisk()) {
            $files['Condicionado del Plan.pdf'] = $condicionado->absolutePath();
        } else {
            $warnings[] = 'El condicionado de este plan aún no ha sido configurado.';
        }

        return ['files' => $files, 'warnings' => $warnings];
    }

    /**
     * Mismos documentos que collect(), empaquetados en un zip -para la
     * descarga directa desde el panel, donde un solo archivo es más cómodo
     * que varios.
     *
     * @return array{path: string, filename: string, warnings: array<int, string>}
     */
    public static function build(Affiliation $affiliation): array
    {
        $collected = static::collect($affiliation);

        $zipPath = Storage::disk('local')->path('welcome-kits/'.Str::uuid().'.zip');
        Storage::disk('local')->makeDirectory('welcome-kits');

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($collected['files'] as $nameInZip => $absolutePath) {
            $zip->addFile($absolutePath, $nameInZip);
        }

        $zip->close();

        return [
            'path' => $zipPath,
            'filename' => "Kit de Bienvenida - {$affiliation->code}.zip",
            'warnings' => $collected['warnings'],
        ];
    }
}
