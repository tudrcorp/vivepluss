<?php

declare(strict_types=1);

namespace App\Support\WhiteCompanies;

use App\Exceptions\WhiteCompanyNegotiatedRateMissingException;
use App\Models\Affiliate;
use App\Models\Affiliation;
use App\Models\IntegracorpAgeRange;
use App\Models\IntegracorpFee;
use App\Models\WhiteCompany;
use App\Models\WhiteCompanyFee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Resuelve el precio de venta / neta pactados con Integracorp para una
 * afiliación de ViVEplus, sumando el aporte de cada persona (titular y/o
 * afiliados) contra el catálogo propio de Integracorp (`IntegracorpFee`,
 * `IntegracorpAgeRange`) y la matriz de tarifas negociadas (`WhiteCompanyFee`)
 * de la empresa aliada correspondiente (`Affiliation::white_company_id`).
 *
 * ViVEplus es siempre la parte aliada frente a Integracorp, así que a
 * diferencia del resolver original de Integracorp esta versión no evalúa si
 * la afiliación "es aliada": siempre lo es y lanza si falta alguna tarifa.
 */
final class WhiteCompanyNegotiatedRateResolver
{
    private const INITIAL_PLAN_ID = 1;

    public function settlementForAffiliation(Affiliation $affiliation): WhiteCompanyPaymentSettlement
    {
        if ($affiliation->white_company_id === null) {
            throw WhiteCompanyNegotiatedRateMissingException::make(
                'N/A',
                is_string($affiliation->code) ? $affiliation->code : null,
                $affiliation->plan_id !== null ? (int) $affiliation->plan_id : null,
                $affiliation->coverage_id !== null ? (int) $affiliation->coverage_id : null,
            );
        }

        $settlement = $this->fromSnapshot($affiliation) ?? $this->fromMatrix($affiliation);

        $this->snapshot($affiliation, $settlement);

        return $settlement;
    }

    /**
     * Reutiliza la tarifa ya congelada en la afiliación (primer pago) en vez de
     * recalcularla en cada cuota, para que no cambie si la matriz se edita
     * después de la primera aprobación.
     */
    private function fromSnapshot(Affiliation $affiliation): ?WhiteCompanyPaymentSettlement
    {
        if ($affiliation->white_company_sale_price === null || $affiliation->white_company_neta === null) {
            return null;
        }

        return new WhiteCompanyPaymentSettlement(
            annualSalePrice: (float) $affiliation->white_company_sale_price,
            annualNeta: (float) $affiliation->white_company_neta,
            paymentFrequency: (string) ($affiliation->payment_frequency ?? 'ANUAL'),
            whiteCompanyId: (int) $affiliation->white_company_id,
            feeId: $affiliation->white_company_fee_id !== null ? (int) $affiliation->white_company_fee_id : null,
        );
    }

    private function fromMatrix(Affiliation $affiliation): WhiteCompanyPaymentSettlement
    {
        $companyName = (string) (WhiteCompany::find($affiliation->white_company_id)?->name ?? 'N/A');

        $rates = WhiteCompanyFee::query()
            ->where('white_company_id', $affiliation->white_company_id)
            ->where('status', 'ACTIVO')
            ->get()
            ->keyBy('fee_id');

        $affiliates = $affiliation->relationLoaded('affiliates')
            ? $affiliation->affiliates
            : $affiliation->affiliates()->get();

        $lines = [];

        if ($affiliates->isEmpty()) {
            $line = $this->lineForTitular($affiliation, $companyName, $rates);
            $persons = max(1, (int) ($affiliation->family_members ?: 1));

            for ($i = 0; $i < $persons; $i++) {
                $lines[] = $line;
            }
        } else {
            foreach ($affiliates as $affiliate) {
                $lines[] = $this->lineForAffiliate($affiliation, $affiliate, $companyName, $rates);
            }
        }

        return WhiteCompanyPaymentSettlement::fromPersonLines(
            $lines,
            (string) ($affiliation->payment_frequency ?? 'ANUAL'),
            (int) $affiliation->white_company_id,
        );
    }

    /**
     * @param  Collection<int|string, WhiteCompanyFee>  $rates
     * @return array{sale_price: float, neta: float, fee_id: int}
     */
    private function lineForTitular(Affiliation $affiliation, string $companyName, Collection $rates): array
    {
        $planId = (int) ($affiliation->plan_id ?? 0);
        $isInitial = $planId === self::INITIAL_PLAN_ID;
        $coverageId = $isInitial ? null : ($affiliation->coverage_id !== null ? (int) $affiliation->coverage_id : null);

        return $this->lineForPerson(
            $affiliation,
            $companyName,
            $rates,
            $planId,
            $coverageId,
            $this->titularAge($affiliation),
            trim((string) $affiliation->full_name_ti) !== '' ? (string) $affiliation->full_name_ti : 'titular',
        );
    }

    /**
     * @param  Collection<int|string, WhiteCompanyFee>  $rates
     * @return array{sale_price: float, neta: float, fee_id: int}
     */
    private function lineForAffiliate(Affiliation $affiliation, Affiliate $affiliate, string $companyName, Collection $rates): array
    {
        $planId = (int) ($affiliate->plan_id ?: $affiliation->plan_id ?: 0);
        $isInitial = $planId === self::INITIAL_PLAN_ID;
        $coverageId = $isInitial
            ? null
            : ($affiliate->coverage_id !== null
                ? (int) $affiliate->coverage_id
                : ($affiliation->coverage_id !== null ? (int) $affiliation->coverage_id : null));

        $personName = trim((string) $affiliate->full_name);
        $personName = $personName !== '' ? $personName : 'afiliado #'.$affiliate->getKey();

        return $this->lineForPerson(
            $affiliation,
            $companyName,
            $rates,
            $planId,
            $coverageId,
            $this->affiliateAge($affiliate),
            $personName,
        );
    }

    /**
     * @param  Collection<int|string, WhiteCompanyFee>  $rates
     * @return array{sale_price: float, neta: float, fee_id: int}
     */
    private function lineForPerson(
        Affiliation $affiliation,
        string $companyName,
        Collection $rates,
        int $planId,
        ?int $coverageId,
        ?int $age,
        string $personName,
    ): array {
        $fee = $planId > 0 && $age !== null
            ? $this->resolveFee($planId, $coverageId, $age)
            : null;

        $rate = $fee instanceof IntegracorpFee ? $rates->get($fee->id) : null;

        if (! $rate instanceof WhiteCompanyFee) {
            throw WhiteCompanyNegotiatedRateMissingException::forPerson(
                $companyName,
                is_string($affiliation->code) ? $affiliation->code : null,
                $personName,
                $planId > 0 ? $planId : null,
                $coverageId,
                $age,
            );
        }

        return [
            'sale_price' => (float) $rate->sale_price,
            'neta' => (float) $rate->neta,
            'fee_id' => (int) $rate->fee_id,
        ];
    }

    private function resolveFee(int $planId, ?int $coverageId, int $age): ?IntegracorpFee
    {
        $isInitial = $planId === self::INITIAL_PLAN_ID;

        $query = IntegracorpFee::query()->with('ageRange');

        if ($isInitial) {
            $query->where('age_range_id', 1);
        } else {
            if ($coverageId === null) {
                return null;
            }

            $query->where('coverage_id', $coverageId);
        }

        return $query->get()->first(fn (IntegracorpFee $fee): bool => $this->feeMatchesAge($age, $fee, $planId));
    }

    private function feeMatchesAge(int $age, IntegracorpFee $fee, int $planId): bool
    {
        $ageRange = $fee->ageRange;

        if (! $ageRange instanceof IntegracorpAgeRange) {
            return false;
        }

        if (! $this->ageMatchesRange($age, $ageRange)) {
            return false;
        }

        return $planId === self::INITIAL_PLAN_ID || (int) $ageRange->plan_id === $planId;
    }

    private function ageMatchesRange(int $age, IntegracorpAgeRange $ageRange): bool
    {
        if (filled($ageRange->age_init) && filled($ageRange->age_end)) {
            return $age >= (int) $ageRange->age_init && $age <= (int) $ageRange->age_end;
        }

        if (preg_match('/(\d+)\s*(?:a|–|-|—|hasta)\s*(\d+)/iu', (string) $ageRange->range, $matches) === 1) {
            return $age >= (int) $matches[1] && $age <= (int) $matches[2];
        }

        return false;
    }

    private function titularAge(Affiliation $affiliation): ?int
    {
        $birth = $this->parseDate($affiliation->birth_date_ti);

        if ($birth instanceof Carbon) {
            return (int) $birth->age;
        }

        return filled($affiliation->age) && is_numeric($affiliation->age) ? (int) $affiliation->age : null;
    }

    private function affiliateAge(Affiliate $affiliate): ?int
    {
        if (filled($affiliate->age) && is_numeric($affiliate->age)) {
            return (int) $affiliate->age;
        }

        $birth = $this->parseDate($affiliate->birth_date);

        return $birth instanceof Carbon ? (int) $birth->age : null;
    }

    private function parseDate(mixed $date): ?Carbon
    {
        if (blank($date)) {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $date))->startOfDay();
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse((string) $date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function snapshot(Affiliation $affiliation, WhiteCompanyPaymentSettlement $settlement): void
    {
        if ($affiliation->white_company_sale_price !== null && $affiliation->white_company_neta !== null) {
            return;
        }

        $affiliation->white_company_sale_price = $settlement->annualSalePrice;
        $affiliation->white_company_neta = $settlement->annualNeta;
        $affiliation->white_company_fee_id = $settlement->feeId;
        $affiliation->save();
    }
}
