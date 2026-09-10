<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `paid_memberships`, `collections` y `sales` son tablas del schema compartido de
 * Integracorp: vienen con el dump de cada entorno y no tienen migración acá. Por
 * eso cada cambio se envuelve en `hasTable` además de `hasColumn` — sin el
 * primero, la migración explota en el sqlite de los tests, donde esas tablas no
 * existen, y se lleva puesta toda la suite Feature.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paid_memberships') && ! Schema::hasColumn('paid_memberships', 'white_company_id')) {
            Schema::table('paid_memberships', function (Blueprint $table) {
                $table->integer('white_company_id')->nullable()->after('affiliation_id');
            });
        }

        if (Schema::hasTable('collections') && ! Schema::hasColumn('collections', 'white_company_id')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->integer('white_company_id')->nullable()->after('affiliation_code');
            });
        }

        if (Schema::hasTable('sales') && ! Schema::hasColumn('sales', 'white_company_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->integer('white_company_id')->nullable()->after('affiliation_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('paid_memberships') && Schema::hasColumn('paid_memberships', 'white_company_id')) {
            Schema::table('paid_memberships', function (Blueprint $table) {
                $table->dropColumn('white_company_id');
            });
        }

        if (Schema::hasTable('collections') && Schema::hasColumn('collections', 'white_company_id')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('white_company_id');
            });
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'white_company_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('white_company_id');
            });
        }
    }
};
