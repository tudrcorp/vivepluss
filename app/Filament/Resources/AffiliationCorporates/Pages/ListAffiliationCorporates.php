<?php

namespace App\Filament\Resources\AffiliationCorporates\Pages;

use App\Filament\Resources\AffiliationCorporates\AffiliationCorporateResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAffiliationCorporates extends ListRecords
{
    protected static string $resource = AffiliationCorporateResource::class;

    protected static ?string $title = 'Afiliaciones Corporativas';

    public function getHeader(): ?View
    {
        return view('filament.resources.affiliation-corporates.list-header');
    }
}
