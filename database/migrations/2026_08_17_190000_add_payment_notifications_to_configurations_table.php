<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mismo patrón que 2026_08_13_190000_add_document_notifications_to_configurations_table,
 * para el aviso de comprobante de pago cargado (ver AffiliationController::notifyPaymentProofUploaded).
 */
return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasColumn('configurations', 'payment_notifications_enabled')) {
            return;
        }

        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->boolean('payment_notifications_enabled')->default(false)->after('document_notification_phones');
            $table->json('payment_notification_emails')->nullable()->after('payment_notifications_enabled');
            $table->json('payment_notification_phones')->nullable()->after('payment_notification_emails');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('configurations', function (Blueprint $table) {
            $table->dropColumn([
                'payment_notifications_enabled',
                'payment_notification_emails',
                'payment_notification_phones',
            ]);
        });
    }
};
