<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_reconciliations')) {
            return;
        }

        Schema::create('credit_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->default('white_company')->index();
            $table->unsignedBigInteger('white_company_id')->nullable();
            $table->unsignedBigInteger('agency_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('paid_membership_id')->nullable();
            $table->unsignedBigInteger('paid_membership_corporate_id')->nullable();
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->string('affiliation_kind')->nullable()->index();
            $table->unsignedBigInteger('affiliation_id')->nullable();
            $table->unsignedBigInteger('affiliation_corporate_id')->nullable();
            $table->string('affiliation_code')->nullable()->index();
            $table->text('affiliation_information')->nullable();
            $table->unsignedInteger('affiliates_count')->default(0);
            $table->decimal('annual_amount', 14, 2)->default(0);
            $table->decimal('total_to_pay', 14, 2)->default(0);
            $table->string('payment_frequency')->nullable();
            $table->string('collection_invoice_number')->nullable()->index();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Estas tablas "legacy" (afiliaciones, agencias, agentes, marcas blancas,
            // comprobantes de pago, cobros) no tienen migración propia en este repo:
            // ya existen en el volcado de base de datos que provee cada entorno. Si
            // esta migración llega a ejecutarse en un entorno nuevo, se asume que ese
            // volcado ya se cargó antes (igual que el resto de las tablas del sistema).
            $table->foreign('white_company_id')->references('id')->on('white_companies')->nullOnDelete();
            $table->foreign('agency_id')->references('id')->on('agencies')->nullOnDelete();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->foreign('paid_membership_id')->references('id')->on('paid_memberships')->nullOnDelete();
            $table->foreign('paid_membership_corporate_id')->references('id')->on('paid_membership_corporates')->nullOnDelete();
            $table->foreign('collection_id')->references('id')->on('collections')->nullOnDelete();
            $table->foreign('affiliation_id')->references('id')->on('affiliations')->nullOnDelete();
            $table->foreign('affiliation_corporate_id')->references('id')->on('affiliation_corporates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_reconciliations');
    }
};
