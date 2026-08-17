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
 *
 * Si se indica $documentPath, el mensaje se manda como adjunto nativo de
 * WhatsApp (endpoint /messages/document de ultramsg) codificado en base64
 * -no como URL- porque en local (dominios .test de Herd) ultramsg no puede
 * alcanzar nuestro servidor para descargar el archivo; $body se usa como
 * caption del archivo. $documentPath se lee en el propio job, no se borra
 * (varios teléfonos pueden compartir el mismo archivo temporal).
 */
class SendAffiliationDocumentWhatsApp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $phone,
        public string $body,
        public ?string $documentPath = null,
        public ?string $documentFilename = null,
    ) {}

    public function handle(): void
    {
        $isDocument = filled($this->documentPath);

        if ($isDocument && ! is_file($this->documentPath)) {
            Log::error('No se pudo enviar el documento por WhatsApp: el archivo ya no existe.', [
                'phone' => $this->phone,
                'path' => $this->documentPath,
            ]);

            return;
        }

        $params = $isDocument
            ? [
                'token' => config('parametros.TOKEN_WHATSAPP'),
                'to' => $this->phone,
                'document' => 'data:'.(mime_content_type($this->documentPath) ?: 'application/octet-stream').';base64,'.base64_encode(file_get_contents($this->documentPath)),
                'filename' => $this->documentFilename ?? 'documento.pdf',
                'caption' => $this->body,
            ]
            : [
                'token' => config('parametros.TOKEN_WHATSAPP'),
                'to' => $this->phone,
                'body' => $this->body,
            ];

        $url = $isDocument
            ? config('parametros.CURLOPT_URL_WHATSAPP_DOCUMENT')
            : config('parametros.CURLOPT_URL_WHATSAPP');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
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
                'is_document' => $isDocument,
                'error' => $error,
                'status_code' => $statusCode,
                'response' => $response,
            ]);
        }
    }
}
