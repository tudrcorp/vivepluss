<?php

namespace App\Models;

use App\Jobs\SendEmailPropuestaEconomicaEspecialCor;
use App\Jobs\SendEmailPropuestaEconomicaIdealCor;
use App\Jobs\SendEmailPropuestaEconomicaInicialCor;
use App\Jobs\SendEmailPropuestaEconomicaMultiple;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CorporateQuote extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $table = 'corporate_quotes';

    /**
     * Ver App\Models\IndividualQuote::ID_OFFSET (mismo mecanismo, mismo
     * valor): un id por debajo de esto en affiliation_corporates.corporate_quote_id
     * pertenece a la tabla legacy de Integracorp. Ver App\Support\CorporateQuoteResolver.
     */
    public const ID_OFFSET = 1_000_000;

    protected $fillable = [
        'code',
        'code_agent',
        'state_id',
        'country_id',
        'region',
        'city_id',
        'code_agency',
        'count_days',
        'full_name',
        'rif',
        'email',
        'phone',
        'status',
        'created_by',
        'agent_id',
        'corporate_quote_request_id',
        'owner_code',
        'plan',
        'observations',
        'data_doc',
        'observation_dress_tailor',
        'type',
        'ownerAccountManagers',
        'white_company_id',

    ];

    /**
     * Get the user that owns the Agent
     *
     * @return BelongsTo
     */
    public function accountManager()
    {
        return $this->hasOne(User::class, 'id', 'ownerAccountManagers');
    }

    /**
     * Get all of the comments for the IndividualQuote
     */
    public function detailCoporateQuotes(): HasMany
    {
        return $this->hasMany(DetailCorporateQuote::class, 'corporate_quote_id', 'id');
    }

    /**
     * Get all of the comments for the IndividualQuote
     */
    public function corporateQuoteData(): HasMany
    {
        return $this->hasMany(CorporateQuoteData::class, 'corporate_quote_id', 'id');
    }

    /**
     * Get all of the comments for the IndividualQuote
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLogCorpQuote::class, 'corporate_quote_id', 'id');
    }

    public function corporateQuoteObservations(): HasMany
    {
        return $this->hasMany(CorporateQuoteObservation::class)->orderByDesc('created_at');
    }

    public function corporateQuoteRequest()
    {
        return $this->belongsTo(CorporateQuoteRequest::class);
    }

    // hasOne
    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class, 'id', 'agent_id');
    }

    public function state(): HasOne
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    /**
     * Funciones para la ejecucion de jobs
     * para el envio de los correos de propuesta economica
     *
     * @author TuDrEnCasa
     *
     * @param  array  $details
     * @return void
     */
    public function sendPropuestaEconomicaPlanInicial($details)
    {
        try {

            // code...
            $collect = collect($details['data'][0]);
            // dd($collect);

            /**
             * JOB
             */
            // SendEmailPropuestaEconomicaInicialCor::dispatch($details, $collect, Auth::user());
            ini_set('memory_limit', '2048M');

            $name_user = Auth::user()->name;
            $pdf = Pdf::loadView('documents.propuesta-economica-cor', compact('details', 'collect', 'name_user'));
            $name_pdf = $details['code'].'.pdf';
            $quotesDirectory = public_path('storage/quotes');
            File::ensureDirectoryExists($quotesDirectory);
            $pdf->save($quotesDirectory.DIRECTORY_SEPARATOR.$name_pdf);

        } catch (\Throwable $th) {
            // throw $th;
            Notification::make()
                ->title('Error')
                ->body($th->getMessage())
                ->error()
                ->send();
        }

        /**
         * Despues de guardar el pdf lo enviamos por email
         * ----------------------------------------------------------------------------------------------------
         */
        // Mail::to($details['email'])->send(new SendMailPropuestaPlanInicial($details['name'], $name_pdf));
    }

    /**
     * Funciones para la ejecucion de jobs
     * para el envio de los correos de propuesta economica
     *
     * @author TuDrEnCasa
     *
     * @param  array  $details
     * @return void
     */
    public function sendPropuestaEconomicaPlanIdeal($details)
    {
        $collect = collect($details['data']);
        $group_collect = $collect->groupBy('age_range');

        /**
         * JOB
         */
        SendEmailPropuestaEconomicaIdealCor::dispatch($details, $group_collect, Auth::user());
    }

    /**
     * Funciones para la ejecucion de jobs
     * para el envio de los correos de propuesta economica
     *
     * @author TuDrEnCasa
     *
     * @param  array  $details
     * @return void
     */
    public function sendPropuestaEconomicaPlanEspecial($details)
    {

        $collect = collect($details['data']);
        $group_collect = $collect->groupBy('age_range');

        // dd($details, $group_collect);

        /**
         * JOB
         */
        SendEmailPropuestaEconomicaEspecialCor::dispatch($details, $group_collect, Auth::user());
    }

    public function isAffiliated($id): bool
    {
        return $this->where('id', $id)->exists();
    }

    /**
     * Propuesta de un plan asignado por Integracorp: misma forma que la del plan
     * ideal (agrupada por rango de edad); lo específico de cada plan lo resuelve
     * la vista, que arma sus columnas desde las coberturas reales.
     */
    public function sendPropuestaEconomicaPlanAsignado($details)
    {
        $this->sendPropuestaEconomicaPlanIdeal($details);
    }

    public function sendPropuestaEconomicaMultiple($collect_final)
    {
        // dd($collect_final);
        try {

            /**
             * JOB
             */
            Log::info($collect_final);

            $details_generals = [];
            for ($i = 0; $i < count($collect_final); $i++) {
                $details_generals = [
                    'code' => $collect_final[$i]['code'],
                    'name' => $collect_final[$i]['name'],
                    'email' => $collect_final[$i]['email'],
                    'phone' => $collect_final[$i]['phone'],
                    'date' => $collect_final[$i]['date'],
                ];
                break;
            }

            SendEmailPropuestaEconomicaMultiple::dispatch($collect_final, $details_generals, Auth::user());
            // code...
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
