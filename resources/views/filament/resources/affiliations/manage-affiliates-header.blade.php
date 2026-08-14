<div class="relative max-w-2xl">
    <a
        href="{{ \App\Filament\Resources\Affiliations\AffiliationResource::getUrl('index', panel: 'viveadmin') }}"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
    >
        {{ svg('heroicon-o-arrow-left', 'h-4 w-4') }}
        Volver a afiliaciones
    </a>

    <p class="mt-4 text-xs font-semibold tracking-[0.2em] text-primary-600 uppercase dark:text-primary-400">
        Gestión de Afiliados
    </p>

    <h1 class="mt-3 text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl dark:text-white">
        AFILIADOS
        <span class="block bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent dark:from-primary-300 dark:to-primary-500">
            {{ $affiliation->code }}
        </span>
    </h1>

    <p class="mt-4 max-w-xl text-sm text-gray-500 sm:text-base dark:text-gray-400">
        Consulta y edita la información principal de las personas afiliadas a esta afiliación.
    </p>
</div>
