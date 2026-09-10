<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla propia de ViVEplus para cotizaciones individuales, separada de
 * `individual_quotes` de Integracorp (conexión `mysql`, BD `operaciones`),
 * que sigue existiendo intacta y deja de recibir escrituras nuevas de
 * ViVEplus. El AUTO_INCREMENT arranca en 1.000.001 -muy por encima del
 * máximo actual de la tabla legacy- para que el rango de IDs nunca se
 * solape entre las dos tablas: así App\Support\IndividualQuoteResolver
 * puede decidir el origen de una cotización comparando solo el id, sin
 * agregar ninguna columna a `affiliations` ni tocar el schema legacy.
 */
return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        Schema::connection($this->connection)->create('individual_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('owner_code', 100)->nullable();
            $table->string('code_agency', 50)->nullable();
            /**
             * Nullable a propósito: el INSERT ocurre sin code (MySQL asigna
             * el id primero) y App\Observers\IndividualQuoteObserver lo
             * completa justo después con un UPDATE atómico basado en ese
             * id. Una UNIQUE key en MySQL permite múltiples NULL sin
             * conflicto, así que no hay ventana insegura entre ambos pasos.
             */
            $table->string('code')->nullable()->unique();
            $table->integer('agent_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('count_days')->default(0);
            $table->string('region', 50)->nullable();
            $table->string('full_name')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('ACTIVA');
            $table->string('created_by');
            $table->string('owner_agent', 100)->nullable();
            $table->string('plan', 100)->nullable();
            $table->string('type', 100)->nullable();
            $table->boolean('assignment_status')->default(false);
            $table->integer('age')->nullable();
            $table->integer('ownerAccountManagers')->nullable();
            $table->integer('white_company_id')->nullable();
            $table->timestamps();

            $table->index('owner_code');
            $table->index('code_agency');
            $table->index('agent_id');
            $table->index(['owner_code', 'agent_id']);
            $table->index('full_name');
        });

        Schema::connection($this->connection)->create('detail_individual_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('individual_quote_id')->constrained('individual_quotes')->cascadeOnDelete();
            $table->integer('plan_id');
            $table->integer('age_range_id');
            $table->integer('coverage_id')->nullable();
            $table->integer('total_persons');
            $table->decimal('subtotal_anual', 20, 2)->default(0);
            $table->decimal('subtotal_quarterly', 20, 2)->default(0);
            $table->decimal('subtotal_biannual', 20, 2)->default(0);
            $table->decimal('subtotal_monthly', 20, 2)->default(0);
            $table->decimal('fee', 20, 2)->default(0);
            $table->string('status')->default('ACTIVA-PENDIENTE');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('status_log_in_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('individual_quote_id')->constrained('individual_quotes')->cascadeOnDelete();
            $table->string('updated_by');
            $table->string('observation')->nullable();
            $table->timestamps();
        });

        DB::connection($this->connection)->statement('ALTER TABLE individual_quotes AUTO_INCREMENT = 1000001');
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('status_log_in_quotes');
        Schema::connection($this->connection)->dropIfExists('detail_individual_quotes');
        Schema::connection($this->connection)->dropIfExists('individual_quotes');
    }
};
