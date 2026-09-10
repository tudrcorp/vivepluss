<?php

namespace App\Support;

use App\Models\DetailIndividualQuote;
use App\Models\IndividualQuote;
use App\Models\IntegracorpDetailIndividualQuote;
use App\Models\IntegracorpIndividualQuote;

/**
 * Único lugar de la app que decide si una cotización individual (por su id
 * en `affiliations.individual_quote_id`) es una legacy de Integracorp
 * (`IntegracorpIndividualQuote`, conexión `mysql`) o una propia de ViVEplus
 * (`IndividualQuote`, conexión `mysql_vivepluss`, ids >= ID_OFFSET).
 *
 * Solo lectura: el flujo de creación de afiliaciones ya no ofrece
 * cotizaciones legacy como opción (ver AffiliationForm), así que este
 * resolver solo se usa para seguir mostrando/imprimiendo correctamente las
 * afiliaciones ya existentes que quedaron enlazadas a una cotización
 * legacy antes del corte -nunca para escribir en su tabla.
 */
class IndividualQuoteResolver
{
    public static function isLegacy(int $individualQuoteId): bool
    {
        return $individualQuoteId < IndividualQuote::ID_OFFSET;
    }

    public static function find(?int $individualQuoteId): IndividualQuote|IntegracorpIndividualQuote|null
    {
        if (blank($individualQuoteId)) {
            return null;
        }

        return static::isLegacy($individualQuoteId)
            ? IntegracorpIndividualQuote::find($individualQuoteId)
            : IndividualQuote::find($individualQuoteId);
    }

    /**
     * Opciones de cobertura (id => precio) disponibles para una cotización
     * ya seleccionada, replicando el join original de AffiliationForm pero
     * contra el catálogo `coverages` correcto según el origen: el propio de
     * Integracorp (conexión `mysql`) para cotizaciones legacy, o
     * App\Models\Coverage (`mysql_vivepluss`) para las nuevas -el mismo
     * catálogo que ya usa IndividualQuoteForm al armar la cotización.
     *
     * @return array<int, string>
     */
    public static function coverageOptions(?int $individualQuoteId, ?int $planId): array
    {
        if (blank($individualQuoteId) || blank($planId)) {
            return [];
        }

        $detailClass = static::isLegacy($individualQuoteId)
            ? IntegracorpDetailIndividualQuote::class
            : DetailIndividualQuote::class;

        return $detailClass::join('coverages', 'detail_individual_quotes.coverage_id', '=', 'coverages.id')
            ->join('individual_quotes', 'detail_individual_quotes.individual_quote_id', '=', 'individual_quotes.id')
            ->where('individual_quotes.id', $individualQuoteId)
            ->where('detail_individual_quotes.plan_id', $planId)
            ->select('coverages.id as coverage_id', 'coverages.price as description')
            ->distinct()
            ->get()
            ->pluck('description', 'coverage_id')
            ->all();
    }

    /**
     * Suma de una columna de subtotal (subtotal_anual, etc.) sobre las filas
     * de detalle de una cotización, replicando el filtro original de
     * AffiliationForm (plan + cobertura + detail_ids seleccionados).
     *
     * @param  array<int, int>  $detailIds
     */
    public static function detailSum(
        ?int $individualQuoteId,
        ?int $planId,
        ?int $coverageId,
        array $detailIds,
        string $column,
    ): float {
        if (blank($individualQuoteId) || blank($planId)) {
            return 0;
        }

        $detailClass = static::isLegacy($individualQuoteId)
            ? IntegracorpDetailIndividualQuote::class
            : DetailIndividualQuote::class;

        return (float) $detailClass::query()
            ->where('individual_quote_id', $individualQuoteId)
            ->where('plan_id', $planId)
            ->when($planId != 1, fn ($query) => $query->where('coverage_id', $coverageId))
            ->when(! empty($detailIds), fn ($query) => $query->whereIn('id', $detailIds))
            ->sum($column);
    }
}
