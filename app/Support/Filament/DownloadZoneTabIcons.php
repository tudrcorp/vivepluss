<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Models\Zone;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

final class DownloadZoneTabIcons
{
    public static function forZone(Zone $zone): Heroicon
    {
        $haystack = self::haystackFromZone($zone);

        return self::resolveFromHaystack($haystack, (int) $zone->id);
    }

    public static function forLabel(string $label, int $fallbackZoneId = 0): Heroicon
    {
        $haystack = Str::lower(Str::ascii($label));

        return self::resolveFromHaystack($haystack, $fallbackZoneId);
    }

    public static function forTodosTab(): Heroicon
    {
        return Heroicon::OutlinedSquaresPlus;
    }

    private static function haystackFromZone(Zone $zone): string
    {
        return Str::lower(Str::ascii(implode(' ', array_filter([
            (string) $zone->zone,
            (string) $zone->code,
        ]))));
    }

    private static function resolveFromHaystack(string $haystack, int $fallbackZoneId): Heroicon
    {
        return match (true) {
            str_contains($haystack, 'comunicad') => Heroicon::OutlinedMegaphone,
            str_contains($haystack, 'pago') => Heroicon::OutlinedCreditCard,
            str_contains($haystack, 'viaje') || str_contains($haystack, 'viajes') => Heroicon::OutlinedGlobeAmericas,
            str_contains($haystack, 'recurso') => Heroicon::OutlinedFolderOpen,
            str_contains($haystack, 'metodo') => Heroicon::OutlinedBanknotes,
            str_contains($haystack, 'manual') || str_contains($haystack, 'guia') => Heroicon::OutlinedBookOpen,
            str_contains($haystack, 'formulario') || str_contains($haystack, 'formato') => Heroicon::OutlinedDocumentText,
            default => self::fallbackIconForZoneId($fallbackZoneId),
        };
    }

    private static function fallbackIconForZoneId(int $zoneId): Heroicon
    {
        /** @var list<Heroicon> $palette */
        $palette = [
            Heroicon::OutlinedFolder,
            Heroicon::OutlinedFolderOpen,
            Heroicon::OutlinedDocumentDuplicate,
            Heroicon::OutlinedClipboardDocumentList,
            Heroicon::OutlinedBuildingOffice,
        ];

        $index = $zoneId > 0 ? $zoneId % count($palette) : 0;

        return $palette[$index];
    }
}
