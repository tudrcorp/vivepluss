<?php

use App\Models\Affiliation;
use App\Models\AffiliationCorporate;
use App\Models\AffiliationDocument;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

/**
 * "affiliations"/"affiliation_corporates" no tienen migración en este repo
 * (viven en el volcado de BD de cada entorno, ver comentario en la migración
 * de credit_reconciliations). Para poder probar el webhook sin depender de
 * ese volcado, se crean versiones mínimas de esas tablas solo para estos
 * tests; SQLite hace DDL transaccional, así que RefreshDatabase las revierte
 * junto con el resto de cada test.
 */
beforeEach(function () {
    Schema::create('affiliations', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('status')->default('ACTIVA');
        $table->timestamps();
    });

    Schema::create('affiliation_corporates', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('status')->default('ACTIVA');
        $table->timestamps();
    });

    config()->set('parametros.INTEGRACORP_WEBHOOK_TOKEN', 'test-token');
    config()->set('parametros.INTEGRACORP_WEBHOOK_SECRET', 'test-secret');

    Storage::fake('public');
});

function webhookSignature(array $fields): string
{
    $keys = ['affiliation_type', 'affiliation_code', 'document_type', 'checksum_sha256', 'generated_at', 'idempotency_key'];

    $canonical = implode('&', array_map(
        fn ($key) => $key.'='.($fields[$key] ?? ''),
        $keys
    ));

    return hash_hmac('sha256', $canonical, 'test-secret');
}

function webhookPayload(array $overrides = []): array
{
    $file = File::fake()->create('CER-TDEC-IND-0001.pdf', 5, 'application/pdf');

    return array_merge([
        'affiliation_type' => AffiliationDocument::KIND_INDIVIDUAL,
        'affiliation_code' => 'TDEC-IND-0001',
        'document_type' => AffiliationDocument::TYPE_CERTIFICADO,
        'file' => $file,
        'checksum_sha256' => hash_file('sha256', $file->getPathname()),
        'generated_at' => now()->toIso8601String(),
        'idempotency_key' => (string) Str::uuid(),
    ], $overrides);
}

function postWebhook(array $payload, ?string $token = 'test-token', bool $withSignature = true): TestResponse
{
    $headers = [];

    if ($token !== null) {
        $headers['Authorization'] = 'Bearer '.$token;
    }

    if ($withSignature) {
        $headers['X-Signature'] = webhookSignature($payload);
    }

    return test()->withHeaders($headers)->post('/api/documents/webhook', $payload);
}

test('almacena el documento cuando token, firma y payload son válidos', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    $payload = webhookPayload();

    $response = postWebhook($payload);

    $response->assertStatus(201);

    $document = AffiliationDocument::latestFor('TDEC-IND-0001', AffiliationDocument::TYPE_CERTIFICADO);

    expect($document)->not->toBeNull();
    expect($document->existsOnDisk())->toBeTrue();
    expect($document->idempotency_key)->toBe($payload['idempotency_key']);
});

test('rechaza la petición sin token', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    $response = postWebhook(webhookPayload(), token: null);

    $response->assertStatus(401);
});

test('rechaza la petición con firma inválida', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    $response = postWebhook(webhookPayload(), withSignature: false);

    $response->assertStatus(401);
});

test('rechaza un código de afiliación inexistente', function () {
    $response = postWebhook(webhookPayload(['affiliation_code' => 'NO-EXISTE']));

    $response->assertStatus(422);
});

test('rechaza un archivo que no es PDF', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    $file = File::fake()->create('archivo.txt', 5, 'text/plain');

    $response = postWebhook(webhookPayload([
        'file' => $file,
        'checksum_sha256' => hash_file('sha256', $file->getPathname()),
    ]));

    $response->assertStatus(422);
});

test('una entrega duplicada (mismo idempotency_key) se descarta con 409 y no reprocesa', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    $payload = webhookPayload();

    postWebhook($payload)->assertStatus(201);

    $storedPath = AffiliationDocument::latestFor('TDEC-IND-0001', AffiliationDocument::TYPE_CERTIFICADO)->disk_path;

    // Segundo request con el MISMO idempotency_key (retry de red) pero
    // nuevo archivo/checksum: no debe pisar lo ya almacenado.
    $retryFile = File::fake()->create('CER-TDEC-IND-0001.pdf', 5, 'application/pdf');
    $retryPayload = array_merge($payload, [
        'file' => $retryFile,
        'checksum_sha256' => hash_file('sha256', $retryFile->getPathname()),
    ]);

    postWebhook($retryPayload)->assertStatus(409);

    expect(AffiliationDocument::latestFor('TDEC-IND-0001', AffiliationDocument::TYPE_CERTIFICADO)->disk_path)
        ->toBe($storedPath);
});

test('una versión con generated_at anterior a la almacenada se descarta con 409', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    postWebhook(webhookPayload(['generated_at' => now()->toIso8601String()]))->assertStatus(201);

    $stale = webhookPayload([
        'generated_at' => now()->subDay()->toIso8601String(),
        'idempotency_key' => (string) Str::uuid(),
    ]);

    postWebhook($stale)->assertStatus(409);
});

test('una versión más nueva reemplaza la almacenada', function () {
    Affiliation::create(['code' => 'TDEC-IND-0001']);

    postWebhook(webhookPayload(['generated_at' => now()->subHour()->toIso8601String()]))->assertStatus(201);

    $newer = webhookPayload([
        'generated_at' => now()->toIso8601String(),
        'idempotency_key' => (string) Str::uuid(),
    ]);

    postWebhook($newer)->assertStatus(201);

    expect(AffiliationDocument::latestFor('TDEC-IND-0001', AffiliationDocument::TYPE_CERTIFICADO)->idempotency_key)
        ->toBe($newer['idempotency_key']);
});

test('acepta documentos de afiliaciones corporativas', function () {
    AffiliationCorporate::create(['code' => 'TDEC-COR-0001']);

    $response = postWebhook(webhookPayload([
        'affiliation_type' => AffiliationDocument::KIND_CORPORATE,
        'affiliation_code' => 'TDEC-COR-0001',
        'document_type' => AffiliationDocument::TYPE_CARNET,
    ]));

    $response->assertStatus(201);

    expect(AffiliationDocument::latestFor('TDEC-COR-0001', AffiliationDocument::TYPE_CARNET))->not->toBeNull();
});

test('el comando documents:check-missing detecta afiliaciones sin documentos más allá del umbral', function () {
    config()->set('parametros.DOCUMENT_SYNC_ALERT_HOURS', 48);

    $overdue = Affiliation::create(['code' => 'TDEC-IND-0002']);
    $overdue->forceFill(['created_at' => now()->subDays(3)])->save();

    $recent = Affiliation::create(['code' => 'TDEC-IND-0003']);
    $recent->forceFill(['created_at' => now()->subHour()])->save();

    $excluded = Affiliation::create(['code' => 'TDEC-IND-0004', 'status' => 'EXCLUIDO']);
    $excluded->forceFill(['created_at' => now()->subDays(3)])->save();

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            $codes = collect($context['affiliations'])->pluck('code');

            return $codes->contains('TDEC-IND-0002')
                && ! $codes->contains('TDEC-IND-0003')
                && ! $codes->contains('TDEC-IND-0004');
        });

    $this->artisan('documents:check-missing')->assertSuccessful();
});

test('el comando documents:check-missing no alerta cuando ya existen ambos documentos', function () {
    config()->set('parametros.DOCUMENT_SYNC_ALERT_HOURS', 48);

    $affiliation = Affiliation::create(['code' => 'TDEC-IND-0005']);
    $affiliation->forceFill(['created_at' => now()->subDays(3)])->save();

    Affiliation::create(['code' => 'TDEC-IND-0001']);
    postWebhook(webhookPayload(['affiliation_code' => 'TDEC-IND-0001']))->assertStatus(201);
    postWebhook(webhookPayload([
        'affiliation_code' => 'TDEC-IND-0005',
        'document_type' => AffiliationDocument::TYPE_CERTIFICADO,
    ]))->assertStatus(201);
    postWebhook(webhookPayload([
        'affiliation_code' => 'TDEC-IND-0005',
        'document_type' => AffiliationDocument::TYPE_CARNET,
        'idempotency_key' => (string) Str::uuid(),
    ]))->assertStatus(201);

    Log::shouldReceive('warning')->never();

    $this->artisan('documents:check-missing')->assertSuccessful();
});
