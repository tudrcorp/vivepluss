<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        Schema::connection($this->connection)->create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('zone');
            $table->string('status')->default('ACTIVA');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('zones');
    }
};
