<x-filament-panels::page>
    @php
        $root = $this->getRootAgency();
        $directAgents = $this->getDirectAgents();
        $generals = $this->getGeneralAgencies();
        $agentCountsByAgencyCode = $this->getAgentCountsByAgencyCode();
        $subAgentCountsByAgentId = $this->getSubAgentCountsByAgentId();
        $totalAgentsInGenerals = $generals->sum(fn ($agency) => $agentCountsByAgencyCode[$agency->code] ?? 0);
    @endphp

    @if (! $root)
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-white/10 dark:bg-[#0b0f19]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No se encontró una agencia asociada a tu usuario para construir la estructura comercial.
            </p>
        </div>
    @else
        {{-- Master --}}
        <div class="flex flex-col items-center">
            <div class="w-full max-w-md rounded-2xl border-2 border-primary-500/40 bg-white p-5 shadow-sm dark:bg-[#0b0f19]">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400">
                            {{ svg('heroicon-o-building-library', 'h-6 w-6') }}
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold tracking-wider text-primary-600 uppercase dark:text-primary-400">
                                Agencia Master · {{ $root->code }}
                            </p>
                            <p class="text-base font-bold text-gray-950 dark:text-white">
                                {{ $root->name_corporative }}
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex shrink-0 items-center rounded-full px-2 py-1 text-xs font-semibold {{ $this->statusBadgeClasses($root->status) }}">
                        {{ $root->status }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-lg bg-gray-50 py-2 dark:bg-white/5">
                        <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $generals->count() }}</p>
                        <p class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Generales</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 py-2 dark:bg-white/5">
                        <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $directAgents->count() + $totalAgentsInGenerals }}</p>
                        <p class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Agentes</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 py-2 dark:bg-white/5">
                        <p class="text-sm font-bold text-gray-950 dark:text-white">{{ array_sum($subAgentCountsByAgentId) }}</p>
                        <p class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Sub-Agentes</p>
                    </div>
                </div>
            </div>

            <div class="h-8 w-px bg-gray-300 dark:bg-white/10"></div>
        </div>

        {{-- Agentes directos de Master --}}
        @if ($directAgents->isNotEmpty())
            <div class="flex flex-col items-center">
                <p class="mb-3 text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                    {{ $directAgents->count() }} agente(s) directo(s) de Master
                </p>
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach ($directAgents as $agent)
                        <div class="inline-flex items-center gap-2 rounded-full border border-gray-950/10 bg-white py-1.5 pr-3 pl-1.5 shadow-sm dark:border-white/10 dark:bg-[#0b0f19]">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-500/10 text-primary-600 dark:text-primary-400">
                                {{ svg('heroicon-o-user', 'h-3.5 w-3.5') }}
                            </span>
                            <span class="text-sm font-medium text-gray-950 dark:text-white">{{ $agent->name }}</span>
                            <span class="h-1.5 w-1.5 rounded-full {{ $agent->status === 'ACTIVO' ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-center">
                <div class="h-8 w-px bg-gray-300 dark:bg-white/10"></div>
            </div>
        @endif

        {{-- Buscador de agencias generales --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                Agencias Generales ({{ $generals->count() }})
            </p>

            <div class="relative w-full sm:w-72">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    {{ svg('heroicon-o-magnifying-glass', 'h-4 w-4') }}
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre o código..."
                    class="w-full rounded-lg border-gray-300 bg-white py-2 pr-3 pl-9 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-[#0b0f19] dark:text-white"
                />
            </div>
        </div>

        {{-- Árbol: Agencias Generales -> Agentes -> Sub-Agentes --}}
        <div class="mt-3 space-y-2">
            @forelse ($generals as $agency)
                @php
                    $isAgencyExpanded = in_array($agency->id, $this->expandedGenerals, true);
                    $agencyAgentCount = $agentCountsByAgencyCode[$agency->code] ?? 0;
                @endphp

                <div class="overflow-hidden rounded-2xl border border-gray-950/10 bg-white shadow-sm dark:border-white/10 dark:bg-[#0b0f19]" wire:key="general-{{ $agency->id }}">
                    <button
                        type="button"
                        wire:click="toggleGeneral({{ $agency->id }})"
                        class="flex w-full items-center justify-between gap-3 p-4 text-left transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                {{ svg('heroicon-o-building-office-2', 'h-5 w-5') }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold tracking-wider text-amber-600 uppercase dark:text-amber-400">
                                    Agencia General · {{ $agency->code }}
                                </p>
                                <p class="truncate text-sm font-bold text-gray-950 dark:text-white">
                                    {{ $agency->name_corporative }}
                                </p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $this->statusBadgeClasses($agency->status) }}">
                                {{ $agency->status }}
                            </span>
                            <span class="hidden text-xs text-gray-500 sm:inline dark:text-gray-400">
                                {{ $agencyAgentCount }} agente(s)
                            </span>
                            <span class="text-gray-400 transition-transform {{ $isAgencyExpanded ? 'rotate-180' : '' }}">
                                {{ svg('heroicon-o-chevron-down', 'h-4 w-4') }}
                            </span>
                        </div>
                    </button>

                    @if ($isAgencyExpanded)
                        <div class="border-t border-gray-950/10 bg-gray-50/60 p-4 dark:border-white/10 dark:bg-white/[0.02]">
                            @php $agents = $this->getAgentsForAgency($agency->code); @endphp

                            @forelse ($agents as $agent)
                                @php
                                    $isAgentExpanded = in_array($agent->id, $this->expandedAgents, true);
                                    $subAgentCount = $subAgentCountsByAgentId[$agent->id] ?? 0;
                                @endphp

                                <div class="mb-2 ml-2 overflow-hidden rounded-xl border border-gray-950/10 bg-white last:mb-0 dark:border-white/10 dark:bg-[#0b0f19]" wire:key="agent-{{ $agent->id }}">
                                    <button
                                        type="button"
                                        wire:click="toggleAgent({{ $agent->id }})"
                                        @unless ($subAgentCount > 0) disabled @endunless
                                        class="flex w-full items-center justify-between gap-3 p-3 text-left {{ $subAgentCount > 0 ? 'transition-colors hover:bg-gray-50 dark:hover:bg-white/5' : 'cursor-default' }}"
                                    >
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info-500/10 text-info-600 dark:text-info-400">
                                                {{ svg('heroicon-o-user', 'h-4 w-4') }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-semibold tracking-wider text-info-600 uppercase dark:text-info-400">
                                                    Agente
                                                </p>
                                                <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                                    {{ $agent->name }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-3">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $this->statusBadgeClasses($agent->status) }}">
                                                {{ $agent->status }}
                                            </span>
                                            @if ($subAgentCount > 0)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $subAgentCount }} subagente(s)
                                                </span>
                                                <span class="text-gray-400 transition-transform {{ $isAgentExpanded ? 'rotate-180' : '' }}">
                                                    {{ svg('heroicon-o-chevron-down', 'h-3.5 w-3.5') }}
                                                </span>
                                            @endif
                                        </div>
                                    </button>

                                    @if ($isAgentExpanded && $subAgentCount > 0)
                                        <div class="space-y-1.5 border-t border-gray-950/10 bg-gray-50/60 p-3 dark:border-white/10 dark:bg-white/[0.02]">
                                            @foreach ($this->getSubAgentsFor($agent->id) as $subAgent)
                                                <div class="ml-2 flex items-center justify-between gap-3 rounded-lg border border-gray-950/10 bg-white p-2.5 dark:border-white/10 dark:bg-[#0b0f19]">
                                                    <div class="flex min-w-0 items-center gap-2.5">
                                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-primary-500/10 text-primary-600 dark:text-primary-400">
                                                            {{ svg('heroicon-o-user-circle', 'h-3.5 w-3.5') }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-[9px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                                Sub-Agente
                                                            </p>
                                                            <p class="truncate text-xs font-medium text-gray-950 dark:text-white">
                                                                {{ $subAgent->name }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $this->statusBadgeClasses($subAgent->status) }}">
                                                        {{ $subAgent->status }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="py-2 text-center text-xs text-gray-500 dark:text-gray-400">
                                    Esta agencia general no tiene agentes registrados.
                                </p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-white/10 dark:bg-[#0b0f19]">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if (filled($this->search))
                            No se encontraron agencias generales para "{{ $this->search }}".
                        @else
                            Esta agencia master no tiene agencias generales registradas.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
