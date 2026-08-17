<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('plan_condicionados')) {
            return;
        }

        Schema::connection($this->connection)->create('plan_condicionados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->unique()->constrained('plans')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('disk_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('plan_condicionados');
    }
};
