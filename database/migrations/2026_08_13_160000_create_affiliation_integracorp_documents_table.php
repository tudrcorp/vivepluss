<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliation_integracorp_documents')) {
            return;
        }

        // Nombre distinto a la tabla legacy "affiliation_documents" (adjuntos
        // genéricos de afiliación) para no colisionar con ella: esta tabla es
        // específicamente el registro de certificado/carnet entregados por
        // Integracorp vía el webhook de "Regenerar documentos".
        Schema::create('affiliation_integracorp_documents', function (Blueprint $table) {
            $table->id();
            $table->string('affiliation_kind')->index();
            $table->unsignedBigInteger('affiliation_id')->nullable();
            $table->unsignedBigInteger('affiliation_corporate_id')->nullable();
            $table->string('affiliation_code')->index();
            $table->string('document_type')->index();
            $table->string('disk')->default('public');
            $table->string('disk_path');
            $table->string('checksum_sha256');
            $table->timestamp('generated_at');
            $table->timestamp('received_at');
            $table->string('idempotency_key');
            $table->timestamps();

            // Nombre corto explícito: el autogenerado por Laravel para esta
            // combinación de tabla+columnas supera el límite de 64
            // caracteres de MySQL y el ALTER TABLE fallaba en silencio
            // (la tabla se creaba igual, pero sin esta restricción).
            $table->unique(['affiliation_code', 'document_type'], 'aidocs_code_type_unique');

            $table->foreign('affiliation_id')->references('id')->on('affiliations')->nullOnDelete();
            $table->foreign('affiliation_corporate_id')->references('id')->on('affiliation_corporates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliation_integracorp_documents');
    }
};
