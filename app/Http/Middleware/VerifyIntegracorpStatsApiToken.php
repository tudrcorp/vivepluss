<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege el endpoint de solo lectura por el que Integracorp consulta las
 * cotizaciones individuales propias de ViVEplus para sus estadísticas.
 * Solo Bearer token fijo -a diferencia de VerifyIntegracorpDocumentWebhook,
 * aquí no hay payload entrante que firmar: Integracorp es quien lee, no
 * quien envía datos cuya integridad haya que verificar.
 */
class VerifyIntegracorpStatsApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('parametros.INTEGRACORP_STATS_API_TOKEN');

        if (blank($expectedToken)) {
            Log::error('API de estadísticas de cotizaciones para Integracorp mal configurada: falta el token.');

            return response()->json(['message' => 'Endpoint no configurado.'], 500);
        }

        $token = $request->bearerToken();

        if (blank($token) || ! hash_equals($expectedToken, $token)) {
            Log::warning('API de estadísticas de cotizaciones para Integracorp: token inválido.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'No autorizado.'], 401);
        }

        Log::info('API de estadísticas de cotizaciones consultada por Integracorp.', [
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
