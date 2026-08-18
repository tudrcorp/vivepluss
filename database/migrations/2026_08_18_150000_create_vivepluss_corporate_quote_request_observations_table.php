<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observaciones internas de solicitudes a la medida. CorporateQuoteRequest
 * se queda en mysql (Integracorp); esta bitácora es de ViVEplus y por eso
 * vive en mysql_vivepluss, sin FK —mismo patrón que
 * corporate_quotes.corporate_quote_request_id.
 */
return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('corporate_quote_request_observations')) {
            return;
        }

        $schema->create('corporate_quote_request_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corporate_quote_request_id');
            $table->longText('description');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('corporate_quote_request_id', 'cqr_obs_request_id_idx');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('corporate_quote_request_observations');
    }
};
