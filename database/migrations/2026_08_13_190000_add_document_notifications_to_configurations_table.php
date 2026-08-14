<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasColumn('configurations', 'document_notifications_enabled')) {
            return;
        }

        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->boolean('document_notifications_enabled')->default(false)->after('web_instagram_posts');
            $table->json('document_notification_emails')->nullable()->after('document_notifications_enabled');
            $table->json('document_notification_phones')->nullable()->after('document_notification_emails');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->dropColumn([
                'document_notifications_enabled',
                'document_notification_emails',
                'document_notification_phones',
            ]);
        });
    }
};
