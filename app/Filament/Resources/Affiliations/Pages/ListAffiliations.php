<?php

namespace App\Filament\Resources\Affiliations\Pages;

use App\Filament\Resources\Affiliations\AffiliationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAffiliations extends ListRecords
{
    protected static string $resource = AffiliationResource::class;

    protected static ?string $title = 'Afiliaciones Individuales';

    public function getHeader(): ?View
    {
        return view('filament.resources.affiliations.list-header');
    }
}
