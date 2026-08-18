<?php

use App\Http\Controllers\AffiliationController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * AffiliationController::publicDocumentUrl() solo toca el disco 'public' y no la
 * base de datos, así que se prueba booteando la app sin RefreshDatabase (los tests
 * de Unit no lo aplican, ver tests/Pest.php).
 *
 * No se usa Storage::fake('public') a propósito: el disco falso reemplaza la url
 * del disco por '/storage' relativo y aquí lo que importa es justamente que la url
 * salga absoluta (config/filesystems.php la arma con APP_URL), que es la razón de
 * ser del helper.
 */
uses(TestCase::class);

beforeEach(function () {
    $this->root = storage_path('framework/testing/public-document-url');

    config([
        'filesystems.disks.public.root' => $this->root,
        'filesystems.disks.public.url' => 'https://vivepluss.com/storage',
    ]);

    Storage::forgetDisk('public');
});

afterEach(function () {
    File::deleteDirectory($this->root);
    Storage::forgetDisk('public');
});

it('convierte la ruta relativa de un comprobante en URL pública absoluta', function () {
    Storage::disk('public')->put('comprobantes/abc123.pdf', 'contenido');

    expect(AffiliationController::publicDocumentUrl('comprobantes/abc123.pdf'))
        ->toBe('https://vivepluss.com/storage/comprobantes/abc123.pdf');
});

it('deja intacto lo que no es una ruta publicable', function () {
    expect(AffiliationController::publicDocumentUrl(null))->toBeNull()
        ->and(AffiliationController::publicDocumentUrl(''))->toBe('')
        ->and(AffiliationController::publicDocumentUrl('N/A'))->toBe('N/A');
});

it('no vuelve a publicar una URL absoluta, como la de la nota de crédito', function () {
    $url = 'https://vivepluss.com/storage/notas-credito/NC-AF-0001-20260818120000.pdf';

    expect(AffiliationController::publicDocumentUrl($url))->toBe($url);
});

it('devuelve la ruta original si el archivo no está en el disco public', function () {
    expect(AffiliationController::publicDocumentUrl('comprobantes/inexistente.pdf'))
        ->toBe('comprobantes/inexistente.pdf');
});
