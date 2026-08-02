<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->string('currency_symbol', 10)->default('EUR€')->after('infoColor');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->dropColumn('currency_symbol');
        });
    }
};
