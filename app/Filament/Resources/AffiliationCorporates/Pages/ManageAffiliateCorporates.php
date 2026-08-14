<?php

namespace App\Filament\Resources\AffiliationCorporates\Pages;

use App\Filament\Resources\AffiliationCorporates\AffiliationCorporateResource;
use App\Models\AffiliateCorporate;
use App\Models\AffiliationDocument;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Lista los empleados (AffiliateCorporate) de una afiliación corporativa
 * para descargar el carnet de cada uno -documento por persona, a diferencia
 * del certificado que es uno solo por afiliación-, análoga a ManageAffiliates
 * del lado individual.
 */
class ManageAffiliateCorporates extends ManageRelatedRecords
{
    protected static string $resource = AffiliationCorporateResource::class;

    protected static string $relationship = 'corporateAffiliates';

    public function getTitle(): string
    {
        return 'Empleados de '.$this->getRecord()->code;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nombre')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Apellido')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nro_identificacion')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_company')
                    ->label('Cargo')
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->placeholder('—'),
                TextColumn::make('email')
                    ->label('Correo')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ACTIVO', 'PRE-APROBADA' => 'success',
                        'INACTIVO', 'EXCLUIDO' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('download_carnet')
                    ->label('Descargar Carnet')
                    ->icon('heroicon-s-identification')
                    ->color('info')
                    ->action(function (AffiliateCorporate $record) {
                        $document = AffiliationDocument::latestFor(
                            $this->getRecord()->code,
                            AffiliationDocument::TYPE_CARNET,
                            $record->nro_identificacion
                        );

                        try {
                            if (! $document || ! $document->existsOnDisk()) {
                                throw new \RuntimeException('El carnet de este empleado aún no ha sido entregado por Integracorp.');
                            }

                            return response()->download($document->absolutePath());
                        } catch (\Throwable $th) {
                            Notification::make()
                                ->title('ERROR')
                                ->body($th->getMessage())
                                ->icon('heroicon-s-x-circle')
                                ->iconColor('danger')
                                ->danger()
                                ->send();
                        }
                    })
                    ->hidden(fn (AffiliateCorporate $record) => ! (AffiliationDocument::latestFor(
                        $this->getRecord()->code,
                        AffiliationDocument::TYPE_CARNET,
                        $record->nro_identificacion
                    )?->existsOnDisk() ?? false)),
            ]);
    }
}
