<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('corporate_quote_request_observations')) {
            return;
        }

        Schema::create('corporate_quote_request_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corporate_quote_request_id');
            $table->longText('description');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('corporate_quote_request_id', 'cqr_obs_request_id_fk')
                ->references('id')
                ->on('corporate_quote_requests')
                ->cascadeOnDelete();
            $table->index('corporate_quote_request_id', 'cqr_obs_request_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_quote_request_observations');
    }
};
