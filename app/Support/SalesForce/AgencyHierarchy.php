<?php

declare(strict_types=1);

namespace App\Support\SalesForce;

use App\Models\Agency;
use App\Models\AgencyType;
use App\Models\Configuration;
use Illuminate\Support\Facades\Auth;

/**
 * Quién cuelga de quién en la fuerza de ventas, y qué alcance tiene el usuario
 * conectado sobre esa estructura.
 *
 * La jerarquía no vive en claves foráneas sino en columnas de texto: una
 * agencia general lleva en `agencies.owner_code` el código de su master (y la
 * master se apunta a sí misma), y un agente o sub-agente lleva en
 * `agents.owner_code` el código de la agencia de la que cuelga —los
 * sub-agentes además apuntan a su agente responsable con `owner_agent`. Por
 * eso todo aquí se resuelve por código y no por id.
 *
 * El usuario que representa a una agencia se crea al activarla
 * (`AgenciesTable`): `code_agency` es el código de su propia agencia y
 * `agency_type` es 'MASTER' o 'GENERAL'.
 */
final class AgencyHierarchy
{
    public static function currentCode(): ?string
    {
        $code = Auth::user()?->code_agency;

        return filled($code) ? (string) $code : null;
    }

    /**
     * El administrador de la aliada y la agencia master ven toda la estructura.
     */
    public static function isMaster(): bool
    {
        $user = Auth::user();

        return $user?->is_whiteCompanyAdmin == 1 || $user?->agency_type === 'MASTER';
    }

    public static function isGeneral(): bool
    {
        return Auth::user()?->agency_type === 'GENERAL';
    }

    /**
     * Las agencias generales también administran agentes, pero solo los suyos.
     */
    public static function canManageAgents(): bool
    {
        if (! self::isMaster() && ! self::isGeneral()) {
            return false;
        }

        return self::configuration()?->agents_module_enabled == 1;
    }

    /**
     * Códigos de agencia cuyos agentes puede ver el usuario.
     *
     * Una master ve los suyos más los de cada agencia general que cuelga de
     * ella; una agencia general solo los suyos. La estructura tiene dos
     * niveles (master → general), así que un solo salto basta: no hay
     * generales colgando de generales.
     *
     * @return array<int, string>
     */
    public static function visibleAgencyCodes(): array
    {
        $code = self::currentCode();

        if ($code === null) {
            return [];
        }

        if (! self::isMaster()) {
            return [$code];
        }

        return Agency::query()
            ->where('owner_code', $code)
            ->pluck('code')
            ->push($code)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Opciones del selector "Jerarquía" al registrar un agente: las agencias
     * activas que el usuario puede ver, etiquetadas con su tipo.
     *
     * Incluye la agencia del propio usuario, que antes quedaba fuera porque la
     * lista se armaba solo con `owner_code = code_agency` —a la master la
     * salvaba apuntarse a sí misma, pero una agencia general se quedaba sin
     * ninguna opción y no podía registrar a nadie.
     *
     * `$codigoActual` reinyecta el valor ya guardado del agente que se está
     * editando aunque su agencia esté inactiva o fuera del alcance: sin eso el
     * select saldría vacío y guardar lo dejaría en NULL, sacando al agente de
     * toda la estructura.
     *
     * @return array<string, string>
     */
    public static function assignableAgencyOptions(?string $codigoActual = null): array
    {
        $codes = self::visibleAgencyCodes();

        if ($codes === [] && blank($codigoActual)) {
            return [];
        }

        $tipos = AgencyType::query()->pluck('definition', 'id');

        return Agency::query()
            ->where(function ($query) use ($codes, $codigoActual): void {
                $query->where(fn ($query) => $query->whereIn('code', $codes)->where('status', 'ACTIVO'));

                if (filled($codigoActual)) {
                    $query->orWhere('code', $codigoActual);
                }
            })
            ->orderBy('agency_type_id')
            ->orderBy('code')
            ->get(['code', 'agency_type_id'])
            ->mapWithKeys(fn (Agency $agency): array => [
                $agency->code => trim(($tipos[$agency->agency_type_id] ?? 'AGENCIA').' - '.$agency->code),
            ])
            ->all();
    }

    private static function configuration(): ?Configuration
    {
        return Configuration::where('white_company_id', Auth::user()?->white_company_id)->first()
            ?? Configuration::query()->first();
    }
}
