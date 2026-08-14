<x-filament-widgets::widget>
    <div class="space-y-4">
        @if ($description = $this->getDescription())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif

        <div class="relative overflow-hidden rounded-2xl border border-gray-950/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0b0f19] dark:shadow-black/40">
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.15]"
                style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 10px 10px; color: #34d399;"
            ></div>

            <div class="relative flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                    {{ svg('heroicon-o-banknotes', 'h-6 w-6') }}
                </span>

                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                        Crédito Disponible
                    </p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">
                        {{ $this->getCurrencySymbol() }} {{ $this->getAssignedCredit() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->getStats() as $index => $stat)
                <div
                    class="relative overflow-hidden rounded-2xl border border-gray-950/10 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:border-white/10 dark:bg-[#0b0f19] dark:shadow-black/40 dark:hover:border-white/20"
                >
                    {{-- decorative dot grid --}}
                    <div
                        class="pointer-events-none absolute inset-0 opacity-[0.15]"
                        style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 10px 10px; color: {{ $stat['accent'] }};"
                    ></div>

                    <div class="relative flex items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $stat['label'] }}
                        </h3>

                        @php
                            $comparison = $stat['comparison'];
                            $badgeClasses = match ($comparison['direction']) {
                                'up' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                'down' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                'new' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                                default => 'bg-gray-500/10 text-gray-500 dark:text-gray-400',
                            };
                            $badgeIcon = match ($comparison['direction']) {
                                'up' => 'heroicon-s-arrow-trending-up',
                                'down' => 'heroicon-s-arrow-trending-down',
                                'new' => 'heroicon-s-sparkles',
                                default => 'heroicon-s-minus',
                            };
                        @endphp

                        <div class="flex shrink-0 items-center gap-2">
                            @if ($action = $stat['action'] ?? null)
                                <a
                                    href="{{ $action['url'] }}"
                                    title="{{ $action['label'] }}"
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-950/5 text-gray-500 transition-colors hover:bg-gray-950/10 hover:text-gray-950 dark:bg-white/10 dark:text-gray-400 dark:hover:bg-white/20 dark:hover:text-white"
                                >
                                    {{ svg('heroicon-s-plus', 'h-3.5 w-3.5') }}
                                </a>
                            @endif

                            <span
                                title="{{ $comparison['current'] }} este mes vs {{ $comparison['previous'] }} el mes pasado"
                                class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClasses }}"
                            >
                                {{ svg($badgeIcon, 'h-3.5 w-3.5') }}
                                {{ $comparison['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="relative mt-4">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                            {{ $stat['sublabel'] }}
                        </p>

                        <div class="mt-1.5 flex items-center gap-1.5">
                            <span style="color: {{ $stat['accent'] }};">
                                {{ svg($stat['icon'], 'h-5 w-5') }}
                            </span>
                            <span class="text-2xl font-bold text-gray-950 dark:text-white">
                                {{ $stat['value'] }}
                            </span>
                        </div>

                        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                            {{ $comparison['current'] }} este mes · {{ $comparison['previous'] }} mes pasado
                        </p>
                    </div>

                    <div class="relative mt-4 rounded-lg border border-gray-950/5 bg-gray-950/[0.03] p-3 dark:border-white/5 dark:bg-white/5">
                        <div class="relative h-16 w-full" x-data="{ activeIndex: null }">
                            <svg
                                viewBox="0 0 {{ $stat['chart']['width'] }} {{ $stat['chart']['height'] }}"
                                class="h-full w-full overflow-visible"
                                preserveAspectRatio="none"
                            >
                                <defs>
                                    <linearGradient id="stat-gradient-{{ $index }}" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="{{ $stat['accent'] }}" stop-opacity="0.35" />
                                        <stop offset="100%" stop-color="{{ $stat['accent'] }}" stop-opacity="0" />
                                    </linearGradient>
                                </defs>

                                <path d="{{ $stat['chart']['area'] }}" fill="url(#stat-gradient-{{ $index }})" stroke="none" />

                                <path
                                    d="{{ $stat['chart']['line'] }}"
                                    fill="none"
                                    stroke="{{ $stat['accent'] }}"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-opacity="0.9"
                                />

                                <line
                                    x1="{{ $stat['chart']['peak']['x'] }}"
                                    x2="{{ $stat['chart']['peak']['x'] }}"
                                    y1="0"
                                    y2="{{ $stat['chart']['peak']['y'] }}"
                                    stroke="currentColor"
                                    class="text-gray-950/20 dark:text-white/25"
                                    stroke-width="1"
                                    stroke-dasharray="3 3"
                                />

                                <circle
                                    cx="{{ $stat['chart']['peak']['x'] }}"
                                    cy="{{ $stat['chart']['peak']['y'] }}"
                                    r="3.5"
                                    fill="{{ $stat['accent'] }}"
                                    style="filter: drop-shadow(0 0 6px {{ $stat['accent'] }}) drop-shadow(0 0 10px {{ $stat['accent'] }});"
                                />
                            </svg>

                            {{-- hover targets: one per month, each reveals a tooltip with the exact value --}}
                            @foreach ($stat['chart']['points'] as $i => $point)
                                <button
                                    type="button"
                                    class="absolute z-10 flex h-4 w-4 -translate-x-1/2 -translate-y-1/2 cursor-default items-center justify-center rounded-full focus:outline-none"
                                    style="left: {{ $point['leftPct'] }}%; top: {{ $point['topPct'] }}%;"
                                    @mouseenter="activeIndex = {{ $i }}"
                                    @mouseleave="activeIndex = null"
                                    @focus="activeIndex = {{ $i }}"
                                    @blur="activeIndex = null"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full opacity-0 ring-2 ring-white transition-all duration-100 dark:ring-[#0b0f19]"
                                        :class="activeIndex === {{ $i }} && 'scale-100 opacity-100'"
                                        style="background-color: {{ $stat['accent'] }};"
                                    ></span>
                                </button>

                                <div
                                    x-cloak
                                    x-show="activeIndex === {{ $i }}"
                                    x-transition.opacity.duration.100ms
                                    class="pointer-events-none absolute z-20 -translate-x-1/2 -translate-y-full rounded-lg border border-gray-950/10 bg-white px-2 py-1 text-[11px] whitespace-nowrap shadow-lg dark:border-white/10 dark:bg-gray-900"
                                    style="left: clamp(10%, {{ $point['leftPct'] }}%, 90%); top: {{ $point['topPct'] }}%; margin-top: -10px;"
                                >
                                    <span class="text-gray-500 capitalize dark:text-gray-400">{{ $stat['monthLabels'][$i] }}</span>
                                    <span class="ml-1 font-semibold text-gray-950 dark:text-white">{{ $point['value'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-1 flex justify-between text-[10px] text-gray-400 dark:text-gray-500">
                            @foreach ($stat['monthLabels'] as $month)
                                <span>{{ $month }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
