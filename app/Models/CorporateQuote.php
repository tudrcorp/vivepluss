<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Jobs\SendEmailPropuestaEconomicaIdealCor;
use App\Jobs\SendEmailPropuestaEconomicaMultiple;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Jobs\SendEmailPropuestaEconomicaInicialCor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Jobs\SendEmailPropuestaEconomicaEspecialCor;

class CorporateQuote extends Model
{
    protected $table = 'corporate_quotes';

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
        'ownerAccountManagers'

    ];

    /**
     * Get the user that owns the Agent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountManager()
    {
        return $this->hasOne(User::class, 'id', 'ownerAccountManagers');
    }

    /**
     * Get all of the comments for the IndividualQuote
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detailCoporateQuotes(): HasMany
    {
        return $this->hasMany(DetailCorporateQuote::class, 'corporate_quote_id', 'id');
    }

    /**
     * Get all of the comments for the IndividualQuote
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function corporateQuoteData(): HasMany
    {
        return $this->hasMany(CorporateQuoteData::class, 'corporate_quote_id', 'id');
    }

    /**
     * Get all of the comments for the IndividualQuote
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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

    //hasOne
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
     * @return void
     * @author TuDrEnCasa
     * 
     * @param array $details
     */
    public function sendPropuestaEconomicaPlanInicial($details)
    {
        try {
            
            //code...
            $collect = collect($details['data'][0]);
            // dd($collect);

            /**
             * JOB
             */
            // SendEmailPropuestaEconomicaInicialCor::dispatch($details, $collect, Auth::user());
            ini_set('memory_limit', '2048M');

            $name_user = Auth::user()->name;
            $pdf = Pdf::loadView('documents.propuesta-economica-cor', compact('details', 'collect', 'name_user'));
            $name_pdf = $details['code'] . '.pdf';
            $quotesDirectory = public_path('storage/quotes');
            File::ensureDirectoryExists($quotesDirectory);
            $pdf->save($quotesDirectory . DIRECTORY_SEPARATOR . $name_pdf);
            
        } catch (\Throwable $th) {
            //throw $th;
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
     * @return void
     * @author TuDrEnCasa
     * 
     * @param array $details
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
     * @return void
     * @author TuDrEnCasa
     * 
     * @param array $details
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
            //code...
        } catch (\Throwable $th) {
            dd($th);
        }
    }

}