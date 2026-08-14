<div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
            {{ svg('heroicon-o-arrow-down-tray', 'h-5 w-5') }}
        </span>

        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Documentos
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Consulta y carga los documentos disponibles para los agentes por carpeta.
            </p>
        </div>
    </div>

    <x-filament::actions :actions="$this->getCachedHeaderActions()" />
</div>
