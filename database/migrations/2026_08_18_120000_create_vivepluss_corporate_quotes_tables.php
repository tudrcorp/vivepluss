<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla propia de ViVEplus para cotizaciones corporativas, mismo patrón que
 * 2026_08_17_180000_create_vivepluss_individual_quotes_tables: separada de
 * `corporate_quotes` de Integracorp (conexión `mysql`), que sigue intacta y
 * deja de recibir escrituras nuevas de ViVEplus. AUTO_INCREMENT arranca en
 * 1.000.001 para que los ids nunca se solapen con los legacy, así
 * App\Support\CorporateQuoteResolver decide el origen comparando solo el id.
 *
 * corporate_quote_request_id se deja sin FK: CorporateQuoteRequest se queda
 * en `mysql`, tal cual, no se duplica -esa relación sigue funcionando sola.
 */
return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        Schema::connection($this->connection)->create('corporate_quotes', function (Blueprint $table) {
            $table->id();
            $table->integer('corporate_quote_request_id')->nullable();
            $table->string('owner_code', 100)->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('code_agency')->nullable();
            $table->integer('agent_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->string('region', 50)->nullable();
            $table->integer('count_days')->default(0);
            $table->string('full_name')->nullable();
            $table->string('rif')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->nullable();
            $table->string('created_by');
            $table->string('plan', 100)->nullable();
            $table->string('observations', 100)->nullable();
            $table->string('data_doc', 100)->nullable();
            $table->longText('observation_dress_tailor')->nullable();
            $table->string('type')->nullable();
            $table->integer('ownerAccountManagers')->nullable();
            $table->integer('white_company_id')->nullable();
            $table->timestamps();

            $table->index('rif');
            $table->index('full_name');
        });

        Schema::connection($this->connection)->create('detail_corporate_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_quote_id')->constrained('corporate_quotes')->cascadeOnDelete();
            $table->integer('corporate_quote_request_id')->nullable();
            $table->integer('plan_id')->nullable();
            $table->integer('age_range_id')->nullable();
            $table->integer('coverage_id')->nullable();
            $table->integer('total_persons');
            $table->decimal('subtotal_anual', 8, 2)->default(0);
            $table->decimal('subtotal_quarterly', 8, 2)->default(0);
            $table->decimal('subtotal_biannual', 8, 2)->default(0);
            $table->decimal('subtotal_monthly', 8, 2)->default(0);
            $table->decimal('fee', 8, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('corporate_quote_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_quote_id')->constrained('corporate_quotes')->cascadeOnDelete();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('nro_identificacion');
            $table->string('birth_date')->nullable();
            $table->string('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('condition_medical')->nullable();
            $table->string('initial_date')->nullable();
            $table->string('position_company')->nullable();
            $table->string('address')->nullable();
            $table->string('full_name_emergency')->nullable();
            $table->string('phone_emergency')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('status_log_corp_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('corporate_quote_id')->constrained('corporate_quotes')->cascadeOnDelete();
            $table->string('updated_by');
            $table->string('observation')->nullable();
            $table->timestamps();
        });

        DB::connection($this->connection)->statement('ALTER TABLE corporate_quotes AUTO_INCREMENT = 1000001');
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('status_log_corp_quotes');
        Schema::connection($this->connection)->dropIfExists('corporate_quote_data');
        Schema::connection($this->connection)->dropIfExists('detail_corporate_quotes');
        Schema::connection($this->connection)->dropIfExists('corporate_quotes');
    }
};
