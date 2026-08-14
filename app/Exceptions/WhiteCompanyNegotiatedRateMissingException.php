<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza al aprobar un pago a CRÉDITO cuando falta la tarifa negociada
 * (`WhiteCompanyFee`) para la combinación plan+cobertura+edad de la afiliación
 * o de alguno de sus afiliados. Debe bloquear la aprobación sin dejar nada
 * escrito (ver App\Support\WhiteCompanies\WhiteCompanyNegotiatedRateResolver).
 */
final class WhiteCompanyNegotiatedRateMissingException extends RuntimeException
{
    public static function make(
        string $companyName,
        ?string $affiliationCode,
        ?int $planId,
        ?int $coverageId,
    ): self {
        $affiliation = filled($affiliationCode) ? $affiliationCode : 'N/A';
        $plan = $planId !== null ? (string) $planId : 'N/A';
        $coverage = $coverageId !== null ? (string) $coverageId : 'sin cobertura';

        return new self(
            "La empresa aliada {$companyName} no tiene precio de venta y neta pactados para esta afiliación. "
            ."Afiliación: {$affiliation} · Plan: {$plan} · Cobertura: {$coverage}. "
            .'Acción: cargar la tarifa negociada correspondiente. No se realizó ningún cambio.'
        );
    }

    public static function forPerson(
        string $companyName,
        ?string $affiliationCode,
        string $personName,
        ?int $planId,
        ?int $coverageId,
        ?int $age,
    ): self {
        $affiliation = filled($affiliationCode) ? $affiliationCode : 'N/A';
        $plan = $planId !== null ? (string) $planId : 'N/A';
        $coverage = $coverageId !== null ? (string) $coverageId : 'sin cobertura';
        $ageLabel = $age !== null ? $age.' años' : 'edad desconocida';

        return new self(
            "La empresa aliada {$companyName} no tiene tarifa negociada para {$personName}. "
            ."Afiliación: {$affiliation} · Plan: {$plan} · Cobertura: {$coverage} · {$ageLabel}. "
            .'Acción: cargar esa combinación en la matriz de tarifas negociadas. No se realizó ningún cambio.'
        );
    }
}
