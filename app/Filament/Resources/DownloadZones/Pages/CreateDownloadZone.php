<?php

namespace App\Filament\Resources\DownloadZones\Pages;

use App\Filament\Resources\DownloadZones\DownloadZoneResource;
use App\Models\DownloadZone;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadZone extends CreateRecord
{
    protected static string $resource = DownloadZoneResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $data['status'] ?? 'ACTIVO';

        if (empty($data['position']) && ! empty($data['zone_id'])) {
            $data['position'] = ((int) DownloadZone::query()
                ->where('zone_id', $data['zone_id'])
                ->max('position')) + 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
