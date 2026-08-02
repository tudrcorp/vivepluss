<?php

namespace App\Support\Filament;

use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InternalObservations
{
    /**
     * @return array<int, Textarea>
     */
    public static function formSchema(): array
    {
        return [
            Textarea::make('description')
                ->label('Texto de la observación')
                ->placeholder('Escriba la nota o seguimiento administrativo…')
                ->required()
                ->minLength(2)
                ->maxLength(5000)
                ->rows(5),
        ];
    }

    public static function store(Model $record, string $relationship, array $data): void
    {
        $record->{$relationship}()->create([
            'description' => $data['description'],
            'created_by' => (string) Auth::id(),
        ]);

        Notification::make()
            ->success()
            ->title('Observación guardada')
            ->body('La observación interna se registró correctamente.')
            ->send();
    }
}
