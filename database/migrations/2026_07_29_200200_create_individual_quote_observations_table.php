<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('individual_quote_observations')) {
            return;
        }

        Schema::create('individual_quote_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('individual_quote_id')->constrained('individual_quotes')->cascadeOnDelete();
            $table->longText('description');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('individual_quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_quote_observations');
    }
};
