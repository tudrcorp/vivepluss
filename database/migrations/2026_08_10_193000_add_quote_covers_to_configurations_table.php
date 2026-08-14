<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasColumn('configurations', 'quote_cover_individual')) {
            return;
        }

        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->string('quote_cover_individual')->nullable()->after('brandLogo');
            $table->string('quote_back_cover_individual')->nullable()->after('quote_cover_individual');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->dropColumn(['quote_cover_individual', 'quote_back_cover_individual']);
        });
    }
};
