<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('corporate_quote_observations')) {
            return;
        }

        Schema::create('corporate_quote_observations', function (Blueprint $table) {
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
        Schema::dropIfExists('corporate_quote_observations');
    }
};
