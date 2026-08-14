<?php

namespace App\Services;

use App\Models\Configuration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class IndividualQuotePdfBuilder
{
    /**
     * Genera el PDF de la cotización individual en $destinationPath.
     *
     * Si la marca blanca (white_company_id actual) tiene configurada una portada y
     * una contraportada (Configuration::quote_cover_individual / quote_back_cover_individual),
     * combina esas plantillas de una sola página vía FPDI con una página 2 generada
     * solo con los cálculos, evitando que dompdf tenga que maquetar/decodificar las
     * imágenes de fondo pesadas del diseño estándar en cada generación.
     *
     * Si no hay plantillas configuradas, o el merge falla por cualquier motivo (PDF
     * corrupto, no soportado por el parser gratuito de FPDI, etc.), cae al render
     * completo actual (documents.propuesta-economica) sin cambiar el comportamiento.
     *
     * @param  array<string, mixed>  $viewData  Mismas variables que hoy recibe la vista completa (compact('details', 'collect'|'group_collect', 'name_user')).
     */
    public function build(array $viewData, string $destinationPath): void
    {
        $configuration = Configuration::where('white_company_id', Configuration::currentWhiteCompanyId())->first();

        $coverPath = $this->resolveTemplatePath($configuration?->quote_cover_individual);
        $backCoverPath = $this->resolveTemplatePath($configuration?->quote_back_cover_individual);

        if ($coverPath && $backCoverPath) {
            try {
                $this->buildWithTemplates($coverPath, $backCoverPath, $viewData, $destinationPath);

                return;
            } catch (\Throwable $e) {
                Log::warning('IndividualQuotePdfBuilder: fallo el merge con plantillas FPDI, se usa el render completo de respaldo. '.$e->getMessage());
            }
        }

        $this->buildFullRender($viewData, $destinationPath);
    }

    private function resolveTemplatePath(?string $relativePath): ?string
    {
        if (blank($relativePath)) {
            return null;
        }

        return Storage::disk('public')->exists($relativePath)
            ? Storage::disk('public')->path($relativePath)
            : null;
    }

    private function buildWithTemplates(string $coverPath, string $backCoverPath, array $viewData, string $destinationPath): void
    {
        $fpdi = new Fpdi;

        $fpdi->setSourceFile($coverPath);
        $coverTplId = $fpdi->importPage(1);
        $coverSize = $fpdi->getTemplateSize($coverTplId);

        // FPDI reporta el tamaño de la plantilla en mm; dompdf necesita el tamaño de
        // página en puntos (pt) para que la página 2 generada calce exactamente con
        // el tamaño de la portada/contraportada subidas por el administrador.
        $widthPt = $coverSize['width'] * 72 / 25.4;
        $heightPt = $coverSize['height'] * 72 / 25.4;

        $tempPage2Path = tempnam(sys_get_temp_dir(), 'quote_calc_').'.pdf';

        try {
            Pdf::loadView('documents.propuesta-economica-calculos', $viewData)
                ->setPaper([0, 0, $widthPt, $heightPt])
                ->save($tempPage2Path);

            $fpdi->AddPage($coverSize['orientation'], [$coverSize['width'], $coverSize['height']]);
            $fpdi->useTemplate($coverTplId);

            $fpdi->setSourceFile($tempPage2Path);
            $page2TplId = $fpdi->importPage(1);
            $page2Size = $fpdi->getTemplateSize($page2TplId);
            $fpdi->AddPage($page2Size['orientation'], [$page2Size['width'], $page2Size['height']]);
            $fpdi->useTemplate($page2TplId);

            $fpdi->setSourceFile($backCoverPath);
            $backTplId = $fpdi->importPage(1);
            $backSize = $fpdi->getTemplateSize($backTplId);
            $fpdi->AddPage($backSize['orientation'], [$backSize['width'], $backSize['height']]);
            $fpdi->useTemplate($backTplId);

            $fpdi->Output('F', $destinationPath);
        } finally {
            if (file_exists($tempPage2Path)) {
                unlink($tempPage2Path);
            }
        }
    }

    private function buildFullRender(array $viewData, string $destinationPath): void
    {
        Pdf::loadView('documents.propuesta-economica', $viewData)->save($destinationPath);
    }
}
