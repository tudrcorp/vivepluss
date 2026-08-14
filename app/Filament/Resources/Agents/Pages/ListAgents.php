<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Resources\Agents\AgentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAgents extends ListRecords
{
    protected static string $resource = AgentResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.agents.list-header');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->extraAttributes(['class' => 'ios-action-btn']),
        ];
    }
}
