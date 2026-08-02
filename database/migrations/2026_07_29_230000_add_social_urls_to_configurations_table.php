<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->string('web_url_facebook')->nullable()->after('web_icons_redSocial');
            $table->string('web_url_instagram')->nullable()->after('web_url_facebook');
            $table->string('web_url_twitter')->nullable()->after('web_url_instagram');
            $table->string('web_url_whatsapp')->nullable()->after('web_url_twitter');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->dropColumn([
                'web_url_facebook',
                'web_url_instagram',
                'web_url_twitter',
                'web_url_whatsapp',
            ]);
        });
    }
};
