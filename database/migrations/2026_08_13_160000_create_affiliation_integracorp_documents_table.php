<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('affiliation_integracorp_documents')) {
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

                // Nombres cortos explícitos: los autogenerados por Laravel para esta
                // tabla superan el límite de 64 caracteres de MySQL (el unique y la
                // FK de affiliation_corporate_id). El ALTER TABLE falla y la tabla
                // queda creada a medias.
                $table->unique(['affiliation_code', 'document_type'], 'aidocs_code_type_unique');

                $table->foreign('affiliation_id', 'aidocs_affiliation_id_fk')
                    ->references('id')
                    ->on('affiliations')
                    ->nullOnDelete();
                $table->foreign('affiliation_corporate_id', 'aidocs_affiliation_corporate_id_fk')
                    ->references('id')
                    ->on('affiliation_corporates')
                    ->nullOnDelete();
            });

            return;
        }

        // Reintento tras un fallo a medias: la tabla ya existe (CREATE TABLE
        // sí corrió) pero las FKs con nombre largo no. No hacer early-return
        // o la migración se marcaría como ejecutada sin las restricciones.
        $this->ensureForeignKey('affiliation_id', 'affiliations', 'aidocs_affiliation_id_fk');
        $this->ensureForeignKey('affiliation_corporate_id', 'affiliation_corporates', 'aidocs_affiliation_corporate_id_fk');
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliation_integracorp_documents');
    }

    private function ensureForeignKey(string $column, string $onTable, string $name): void
    {
        if ($this->hasForeignKeyOn($column)) {
            return;
        }

        Schema::table('affiliation_integracorp_documents', function (Blueprint $table) use ($column, $onTable, $name) {
            $table->foreign($column, $name)->references('id')->on($onTable)->nullOnDelete();
        });
    }

    private function hasForeignKeyOn(string $column): bool
    {
        foreach (Schema::getForeignKeys('affiliation_integracorp_documents') as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
