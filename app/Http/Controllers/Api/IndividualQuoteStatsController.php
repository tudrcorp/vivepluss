<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndividualQuote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lectura para las estadísticas propias de Integracorp: ellos ya tienen
 * acceso directo a su propia tabla legacy `individual_quotes` (conexión
 * `mysql`), así que esto solo expone las cotizaciones nuevas de ViVEplus
 * (`mysql_vivepluss`), que de otro modo les serían invisibles. Sin PII
 * (nombre/email/teléfono) -solo lo necesario para agregados- y paginado
 * por cursor de id para poder sincronizar de forma incremental.
 */
class IndividualQuoteStatsController extends Controller
{
    public function index(Request $request)
    {
        $quotes = IndividualQuote::query()
            ->select(['id', 'code', 'status', 'plan', 'code_agency', 'owner_code', 'white_company_id', 'created_at'])
            ->orderBy('id')
            ->cursorPaginate((int) $request->integer('per_page', 100));

        return JsonResource::collection($quotes);
    }
}
