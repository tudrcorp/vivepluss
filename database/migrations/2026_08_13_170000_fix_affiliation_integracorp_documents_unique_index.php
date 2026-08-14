<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La migración original (2026_08_13_160000) intentaba crear un unique
 * (affiliation_code, document_type) cuyo nombre autogenerado supera el
 * límite de 64 caracteres de MySQL; el ALTER TABLE fallaba y la tabla
 * quedaba creada sin esa restricción, sin que la migración se marcara como
 * fallida (Schema::hasTable() ya era true en el reintento). Esta migración
 * la agrega con un nombre corto explícito en los entornos que ya corrieron
 * la original.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('affiliation_integracorp_documents')) {
            return;
        }

        if ($this->indexExists('affiliation_integracorp_documents', 'aidocs_code_type_unique')) {
            return;
        }

        Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
            $table->unique(['affiliation_code', 'document_type'], 'aidocs_code_type_unique');
        });
    }

    public function down(): void
    {
        if (! $this->indexExists('affiliation_integracorp_documents', 'aidocs_code_type_unique')) {
            return;
        }

        Schema::table('affiliation_integracorp_documents', function (Blueprint $table) {
            $table->dropUnique('aidocs_code_type_unique');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();

        return collect($connection->select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($indexName);
    }
};
