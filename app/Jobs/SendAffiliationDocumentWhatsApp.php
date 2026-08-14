<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Envía el aviso de "documento disponible" por WhatsApp vía la API de
 * ultramsg.com, con el mismo patrón de curl que
 * MiddlewareController::notificacionSesionDuplicada (el único envío de
 * WhatsApp que hoy funciona de verdad en este proyecto: la clase de job
 * referenciada en NotificationController para otros flujos no existe).
 */
class SendAffiliationDocumentWhatsApp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $phone,
        public string $body,
    ) {}

    public function handle(): void
    {
        $params = [
            'token' => config('parametros.TOKEN_WHATSAPP'),
            'to' => $this->phone,
            'body' => $this->body,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => config('parametros.CURLOPT_URL_WHATSAPP'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => ['content-type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($error || $statusCode >= 300) {
            Log::error('No se pudo enviar la notificación de WhatsApp de documento disponible.', [
                'phone' => $this->phone,
                'error' => $error,
                'status_code' => $statusCode,
                'response' => $response,
            ]);
        }
    }
}
