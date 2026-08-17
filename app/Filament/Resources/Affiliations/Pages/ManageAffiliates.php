<?php

namespace App\Filament\Resources\Affiliations\Pages;

use App\Filament\Resources\Affiliations\AffiliationResource;
use App\Models\Affiliate;
use App\Models\AffiliationDocument;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ManageAffiliates extends ManageRelatedRecords
{
    protected static string $resource = AffiliationResource::class;

    protected static string $relationship = 'affiliates';

    public function getTitle(): string
    {
        return 'Afiliados de '.$this->getRecord()->code;
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.affiliations.manage-affiliates-header', [
            'affiliation' => $this->getRecord()->loadMissing(['plan', 'coverage']),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function sexOptions(): array
    {
        return [
            'MASCULINO' => 'MASCULINO',
            'FEMENINO' => 'FEMENINO',
        ];
    }

    /**
     * TITULAR se conserva porque el titular de la afiliación también se
     * guarda como un registro de `affiliates`.
     *
     * @return array<string, string>
     */
    public static function relationshipOptions(): array
    {
        return [
            'TITULAR' => 'TITULAR',
            'CONYUGE' => 'CONYUGE',
            'PADRE' => 'PADRE',
            'MADRE' => 'MADRE',
            'HIJO' => 'HIJO',
            'HIJA' => 'HIJA',
            'AMIGO' => 'AMIGO',
            'OTRO' => 'OTRO',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Nombre y Apellido')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nro_identificacion')
                            ->label('Número de Identificación')
                            ->numeric()
                            ->required()
                            ->rules(['regex:/^[0-9]+$/'])
                            ->unique(table: 'affiliates', column: 'nro_identificacion', ignoreRecord: true),
                        Select::make('sex')
                            ->label('Sexo')
                            ->options(static::sexOptions())
                            ->required(),
                        DatePicker::make('birth_date')
                            ->label('Fecha de Nacimiento')
                            ->native()
                            ->required(),
                        Select::make('relationship')
                            ->label('Parentesco')
                            ->options(static::relationshipOptions())
                            ->required(),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(500)
                            ->columnSpan(2),
                    ])->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByRaw("relationship = 'TITULAR' desc"))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nombre y Apellido')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nro_identificacion')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('relationship')
                    ->label('Parentesco')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('sex')
                    ->label('Sexo'),
                TextColumn::make('birth_date')
                    ->label('Fecha de Nacimiento'),
                TextColumn::make('age')
                    ->label('Edad')
                    ->suffix(' años')
                    ->sortable(),
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
                EditAction::make()
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalHeading(fn ($record): string => 'Editar afiliado: '.$record->full_name)
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['birth_date'] = static::normalizeBirthDate($data['birth_date'] ?? null)?->format('Y-m-d');

                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        if (filled($data['birth_date'] ?? null)) {
                            $birthDate = Carbon::createFromFormat('Y-m-d', $data['birth_date']);
                            $data['birth_date'] = $birthDate->format('d-m-Y');
                            $data['age'] = $birthDate->age;
                        }

                        return $data;
                    }),

                Action::make('download_carnet')
                    ->label('Descargar Carnet')
                    ->icon('heroicon-s-identification')
                    ->color('info')
                    ->action(function (Affiliate $record) {
                        $document = AffiliationDocument::latestFor(
                            $this->getRecord()->code,
                            AffiliationDocument::TYPE_CARNET,
                            $record->nro_identificacion
                        );

                        try {
                            if (! $document || ! $document->existsOnDisk()) {
                                throw new \RuntimeException('El carnet de este afiliado aún no ha sido entregado por Integracorp.');
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
                    ->hidden(fn (Affiliate $record) => ! (AffiliationDocument::latestFor(
                        $this->getRecord()->code,
                        AffiliationDocument::TYPE_CARNET,
                        $record->nro_identificacion
                    )?->existsOnDisk() ?? false)),
            ]);
    }

    private static function normalizeBirthDate(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
