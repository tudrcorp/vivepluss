<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('paid_memberships', 'white_company_id')) {
            Schema::table('paid_memberships', function (Blueprint $table) {
                $table->integer('white_company_id')->nullable()->after('affiliation_id');
            });
        }

        if (! Schema::hasColumn('collections', 'white_company_id')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->integer('white_company_id')->nullable()->after('affiliation_code');
            });
        }

        if (! Schema::hasColumn('sales', 'white_company_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->integer('white_company_id')->nullable()->after('affiliation_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paid_memberships', 'white_company_id')) {
            Schema::table('paid_memberships', function (Blueprint $table) {
                $table->dropColumn('white_company_id');
            });
        }

        if (Schema::hasColumn('collections', 'white_company_id')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('white_company_id');
            });
        }

        if (Schema::hasColumn('sales', 'white_company_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('white_company_id');
            });
        }
    }
};
