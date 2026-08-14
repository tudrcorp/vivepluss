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

                $table->foreign('affiliate_id')->references('id')->on('affiliates')->nullOnDelete();
                $table->foreign('affiliate_corporate_id')->references('id')->on('affiliate_corporates')->nullOnDelete();
            });
        }

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
                $table->dropForeign(['affiliate_id']);
                $table->dropForeign(['affiliate_corporate_id']);
                $table->dropColumn(['affiliate_id', 'affiliate_corporate_id', 'affiliate_identification']);
            });
        }
    }

    private function indexExists(string $indexName): bool
    {
        return collect(Schema::getConnection()->select('SHOW INDEX FROM `affiliation_integracorp_documents`'))
            ->pluck('Key_name')
            ->contains($indexName);
    }
};
