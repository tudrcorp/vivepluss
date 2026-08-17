<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\PlanCondicionado;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * El condicionado es un PDF prediseñado por Integracorp, uno distinto por
 * plan, que se incluye en el "Kit de Bienvenida" que un analista puede
 * enviarle a un afiliado (ver AffiliationWelcomeKit). Como no existe ningún
 * CRUD de Plan en este panel, esta pantalla es deliberadamente ligera: solo
 * expone la subida del PDF por cada plan, sin tocar el resto de sus datos.
 */
class CondicionadosPorPlan extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.condicionados-por-plan';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->is_whiteCompanyAdmin) || $user?->agency_type === 'MASTER';
    }

    public function getTitle(): string
    {
        return 'Condicionados por Plan';
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.condicionados-por-plan-header');
    }

    public function mount(): void
    {
        $existing = PlanCondicionado::query()->pluck('disk_path', 'plan_id');

        $this->form->fill(
            Plan::query()
                ->orderBy('description')
                ->get()
                ->mapWithKeys(fn (Plan $plan) => ["condicionado_{$plan->id}" => $existing->get($plan->id)])
                ->all()
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(
                Plan::query()
                    ->orderBy('description')
                    ->get()
                    ->map(fn (Plan $plan) => Fieldset::make($plan->description)
                        ->schema([
                            FileUpload::make("condicionado_{$plan->id}")
                                ->label('Condicionado (PDF)')
                                ->disk('public')
                                ->directory('plan-condicionados')
                                ->acceptedFileTypes(['application/pdf'])
                                ->downloadable()
                                ->openable()
                                ->helperText('PDF prediseñado del condicionado de este plan. Se incluye en el Kit de Bienvenida que se le envía al afiliado.'),
                        ])->columnSpanFull())
                    ->all()
            )
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (Plan::query()->pluck('id') as $planId) {
            $path = $data["condicionado_{$planId}"] ?? null;

            if (blank($path)) {
                PlanCondicionado::where('plan_id', $planId)->delete();

                continue;
            }

            PlanCondicionado::updateOrCreate(
                ['plan_id' => $planId],
                ['disk' => 'public', 'disk_path' => $path],
            );
        }

        Notification::make()
            ->title('Condicionados guardados')
            ->success()
            ->send();
    }
}
