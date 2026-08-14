<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('download_zones')) {
            return;
        }

        Schema::connection($this->connection)->create('download_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->string('document');
            $table->string('status')->default('ACTIVO');
            $table->string('image_icon')->nullable();
            $table->string('description');
            $table->timestamps();

            $table->index(['zone_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('download_zones');
    }
};
