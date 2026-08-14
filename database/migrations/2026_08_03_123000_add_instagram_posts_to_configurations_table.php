<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasColumn('configurations', 'web_instagram_posts')) {
            return;
        }

        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->json('web_instagram_posts')->nullable()->after('web_url_instagram');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->dropColumn('web_instagram_posts');
        });
    }
};
