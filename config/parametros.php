<?php

return [

    'REDIRECT_LOGOUT_EXTERNAL_URL' => 'https://viveplus.test',
    'REDIRECT_LOGOUT_INTERNAL_URL' => 'https://viveplus.test/viveadmin',

    'TOKEN_WHATSAPP' => 'yuvh9eq5kn8bt666',
    'CURLOPT_URL_WHATSAPP' => 'https://api.ultramsg.com/instance117518/messages/chat',
    'CURLOPT_URL_WHATSAPP_DOCUMENT' => 'https://api.ultramsg.com/instance117518/messages/document',

    /**
     * Destinatarios de la notificación de activación automática de afiliación
     * (primer comprobante de pago cargado por el analista). En producción se
     * envía a todos los correos configurados; en cualquier otro ambiente solo
     * llega al correo de desarrollo, para no spamear a administración/negocios
     * mientras se prueba.
     */
    'ACTIVATION_NOTIFICATION_EMAILS' => array_values(array_filter([
        env('MAIL_ACTIVATION_NOTIFY_ADMINISTRACION'),
        env('MAIL_ACTIVATION_NOTIFY_AFILIACIONES'),
        env('MAIL_ACTIVATION_NOTIFY_NEGOCIOS'),
        env('MAIL_ACTIVATION_NOTIFY_HSANCHEZ'),
        env('MAIL_ACTIVATION_NOTIFY_MCASTILLO'),
        env('MAIL_ACTIVATION_NOTIFY_SOLRODRIGUEZ'),
    ])),

    'ACTIVATION_NOTIFICATION_EMAILS_DEV' => array_values(array_filter([
        env('MAIL_ACTIVATION_NOTIFY_DEV'),
    ])),

    /**
     * Credenciales del webhook por el que Integracorp entrega el
     * certificado de afiliación y el carnet del afiliado al ejecutar
     * "Regenerar documentos". INTEGRACORP_WEBHOOK_TOKEN identifica al
     * emisor (header Authorization: Bearer), INTEGRACORP_WEBHOOK_SECRET
     * firma el payload (header X-Signature, HMAC-SHA256). Deben ser
     * secretos distintos entre sí.
     */
    'INTEGRACORP_WEBHOOK_TOKEN' => env('INTEGRACORP_WEBHOOK_TOKEN'),
    'INTEGRACORP_WEBHOOK_SECRET' => env('INTEGRACORP_WEBHOOK_SECRET'),

    /**
     * Horas máximas de tolerancia entre la creación de una afiliación y la
     * llegada del certificado/carnet vía webhook antes de alertar que la
     * sincronización con Integracorp no se completó (ver Fase 5).
     */
    'DOCUMENT_SYNC_ALERT_HOURS' => (int) env('DOCUMENT_SYNC_ALERT_HOURS', 48),

];
