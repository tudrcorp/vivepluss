<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reapuntada a mysql_vivepluss (nunca llegó a ejecutarse en ningún
 * ambiente): esta tabla es para las observaciones de las cotizaciones
 * corporativas propias de ViVEplus creadas en la tabla `corporate_quotes`
 * nueva (2026_08_18_120000_create_vivepluss_corporate_quotes_tables), no
 * para las legacy de Integracorp.
 */
return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        Schema::connection($this->connection)->create('corporate_quote_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_quote_id')->constrained('corporate_quotes')->cascadeOnDelete();
            $table->longText('description');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('corporate_quote_id');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('corporate_quote_observations');
    }
};
