<?php

namespace App\Filament\Pages;

use App\Models\Agency;
use App\Models\Agent;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EstructuraViveplus extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.estructura-viveplus';

    /**
     * @var array<int, int>
     */
    public array $expandedGenerals = [];

    /**
     * @var array<int, int>
     */
    public array $expandedAgents = [];

    public string $search = '';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->is_whiteCompanyAdmin) || $user?->agency_type === 'MASTER';
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.estructura-viveplus-header');
    }

    public function toggleGeneral(int $agencyId): void
    {
        $this->expandedGenerals = $this->toggled($this->expandedGenerals, $agencyId);
    }

    public function toggleAgent(int $agentId): void
    {
        $this->expandedAgents = $this->toggled($this->expandedAgents, $agentId);
    }

    /**
     * @param  array<int, int>  $state
     * @return array<int, int>
     */
    private function toggled(array $state, int $id): array
    {
        return in_array($id, $state, true)
            ? array_values(array_diff($state, [$id]))
            : [...$state, $id];
    }

    public function getRootAgency(): ?Agency
    {
        return once(function () {
            $user = Auth::user();

            return Agency::where('code', $user?->code_agency)->first()
                ?? Agency::where('white_company_id', $user?->white_company_id)->where('agency_type_id', 1)->first()
                ?? Agency::where('agency_type_id', 1)->orderBy('id')->first();
        });
    }

    /**
     * @return Collection<int, Agent>
     */
    public function getDirectAgents(): Collection
    {
        return once(function () {
            $root = $this->getRootAgency();

            if (! $root) {
                return new Collection;
            }

            return Agent::where('owner_code', $root->code)
                ->where('agent_type_id', 2)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @return Collection<int, Agency>
     */
    public function getGeneralAgencies(): Collection
    {
        $root = $this->getRootAgency();

        if (! $root) {
            return new Collection;
        }

        $query = Agency::where('owner_code', $root->code)
            ->where('agency_type_id', 3)
            ->orderBy('name_corporative');

        if (filled($this->search)) {
            $query->where(function ($query) {
                $query->where('name_corporative', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            });
        }

        return $query->get();
    }

    /**
     * Total de agentes directos de cada agencia general, indexado por código de agencia.
     *
     * @return array<string, int>
     */
    public function getAgentCountsByAgencyCode(): array
    {
        return once(fn () => Agent::where('agent_type_id', 2)
            ->whereNotNull('owner_code')
            ->selectRaw('owner_code, count(*) as aggregate')
            ->groupBy('owner_code')
            ->pluck('aggregate', 'owner_code')
            ->all());
    }

    /**
     * Total de sub-agentes de cada agente, indexado por id del agente responsable.
     *
     * @return array<int, int>
     */
    public function getSubAgentCountsByAgentId(): array
    {
        return once(fn () => Agent::where('agent_type_id', 3)
            ->whereNotNull('owner_agent')
            ->selectRaw('owner_agent, count(*) as aggregate')
            ->groupBy('owner_agent')
            ->pluck('aggregate', 'owner_agent')
            ->all());
    }

    /**
     * @return Collection<int, Agent>
     */
    public function getAgentsForAgency(string $agencyCode): Collection
    {
        return Agent::where('owner_code', $agencyCode)
            ->where('agent_type_id', 2)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Agent>
     */
    public function getSubAgentsFor(int $agentId): Collection
    {
        return Agent::where('owner_agent', $agentId)
            ->where('agent_type_id', 3)
            ->orderBy('name')
            ->get();
    }

    public function statusBadgeClasses(?string $status): string
    {
        return match ($status) {
            'ACTIVO' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            'INACTIVO' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
            'POR REVISION' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            default => 'bg-gray-500/10 text-gray-500 dark:text-gray-400',
        };
    }
}
