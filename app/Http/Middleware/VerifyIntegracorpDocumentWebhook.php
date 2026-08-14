<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida las peticiones entrantes del webhook de documentos de afiliación
 * (certificado/carnet) que Integracorp dispara al ejecutar "Regenerar
 * documentos". Doble verificación: token Bearer fijo (identifica al emisor)
 * + firma HMAC-SHA256 del cuerpo (garantiza que el payload no fue alterado
 * en tránsito), ambos con secretos independientes.
 */
class VerifyIntegracorpDocumentWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('parametros.INTEGRACORP_WEBHOOK_TOKEN');
        $secret = config('parametros.INTEGRACORP_WEBHOOK_SECRET');

        if (blank($expectedToken) || blank($secret)) {
            Log::error('Webhook de documentos de Integracorp mal configurado: falta token o secreto.');

            return response()->json(['message' => 'Webhook no configurado.'], 500);
        }

        $token = $request->bearerToken();

        if (blank($token) || ! hash_equals($expectedToken, $token)) {
            Log::warning('Webhook de documentos de Integracorp: token inválido.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $signature = (string) $request->header('X-Signature');
        $expectedSignature = hash_hmac('sha256', $this->canonicalPayload($request), $secret);

        if (blank($signature) || ! hash_equals($expectedSignature, $signature)) {
            Log::warning('Webhook de documentos de Integracorp: firma inválida.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Firma inválida.'], 401);
        }

        return $next($request);
    }

    /**
     * String canónico sobre el que Integracorp calcula la firma: los campos
     * del payload (no el cuerpo multipart crudo, cuyo boundary es aleatorio
     * y frágil de reproducir exactamente en el firmante). El archivo en sí
     * queda cubierto porque su checksum_sha256 forma parte del string
     * firmado y se re-verifica contra el binario recibido.
     */
    private function canonicalPayload(Request $request): string
    {
        $fields = [
            'affiliation_type',
            'affiliation_code',
            'document_type',
            'affiliate_identification',
            'checksum_sha256',
            'generated_at',
            'idempotency_key',
        ];

        $parts = [];

        foreach ($fields as $field) {
            $parts[] = $field.'='.(string) $request->input($field);
        }

        return implode('&', $parts);
    }
}
