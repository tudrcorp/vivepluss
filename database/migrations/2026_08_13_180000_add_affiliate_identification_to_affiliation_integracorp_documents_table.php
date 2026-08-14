<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El carnet es un documento por PERSONA (titular y cada dependiente tienen
 * el suyo), a diferencia del certificado que es uno solo por afiliación.
 * Sin esta columna, dos carnets de distintos miembros de una misma
 * afiliación colisionaban en la restricción única (affiliation_code,
 * document_type) y el segundo pisaba al primero silenciosamente.
 *
 * affiliate_identification usa '' (no NULL) como valor para certificado,
 * porque MySQL trata NULL como "siempre distinto" en índices únicos -con
 * NULL, dos certificados de la misma afiliación no colisionarían y se
 * perdería esa protección-, mientras que '' sí colisiona consigo mismo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('affiliation_integracorp_documents', 'affiliate_identification')) {
            Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('affiliate_id')->nullable()->after('affiliation_corporate_id');
                $table->unsignedBigInteger('affiliate_corporate_id')->nullable()->after('affiliate_id');
                $table->string('affiliate_identification')->default('')->after('affiliate_corporate_id');
            });
        }

        // El nombre autogenerado de affiliate_corporate_id supera los 64
        // caracteres de MySQL; si se mezclan columnas + FKs en el mismo
        // Schema::table y la FK falla, las columnas ya existen y un reintento
        // saltaría este bloque. Se agregan aparte, con nombres cortos.
        $this->ensureForeignKey('affiliate_id', 'affiliates', 'aidocs_affiliate_id_fk');
        $this->ensureForeignKey('affiliate_corporate_id', 'affiliate_corporates', 'aidocs_affiliate_corporate_id_fk');

        if (! $this->indexExists('aidocs_code_type_affiliate_unique')) {
            if ($this->indexExists('aidocs_code_type_unique')) {
                Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
                    $table->dropUnique('aidocs_code_type_unique');
                });
            }

            Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
                $table->unique(['affiliation_code', 'document_type', 'affiliate_identification'], 'aidocs_code_type_affiliate_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('aidocs_code_type_affiliate_unique')) {
            Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
                $table->dropUnique('aidocs_code_type_affiliate_unique');
            });
        }

        if (Schema::hasColumn('affiliation_integracorp_documents', 'affiliate_identification')) {
            Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
                $table->dropForeign('aidocs_affiliate_id_fk');
                $table->dropForeign('aidocs_affiliate_corporate_id_fk');
                $table->dropColumn(['affiliate_id', 'affiliate_corporate_id', 'affiliate_identification']);
            });
        }
    }

    private function ensureForeignKey(string $column, string $onTable, string $name): void
    {
        if (! Schema::hasTable($onTable) || $this->hasForeignKeyOn($column)) {
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

    private function indexExists(string $indexName): bool
    {
        return collect(Schema::getConnection()->select('SHOW INDEX FROM `affiliation_integracorp_documents`'))
            ->pluck('Key_name')
            ->contains($indexName);
    }
};
