<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IndividualQuote extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $table = 'individual_quotes';

    /**
     * Arranque del AUTO_INCREMENT de esta tabla (ver migración
     * 2026_08_17_180000), muy por encima del máximo histórico de la tabla
     * legacy de Integracorp. Cualquier id por debajo de este valor en
     * affiliations.individual_quote_id pertenece a esa tabla legacy, nunca
     * a esta. Ver App\Support\IndividualQuoteResolver.
     */
    public const ID_OFFSET = 1_000_000;

    protected $fillable = [
        'code',
        'email',
        'phone',
        'agent_id',
        'code_agency',
        'full_name',
        'birth_date',
        'status',
        'created_by',
        'state_id',
        'region',
        'code_agent',
        'owner_code',
        'owner_agent',
        'plan',
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
     *
     * @return HasMany
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get all of the comments for the IndividualQuote
     *
     * @return HasMany
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get all of the comments for the IndividualQuote
     */
    public function detailsQuote(): HasMany
    {
        return $this->hasMany(DetailIndividualQuote::class, 'individual_quote_id', 'id');
    }

    /**
     * Get all of the comments for the IndividualQuote
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLogInQuote::class, 'individual_quote_id', 'id');
    }

    /**
     * The servicios that belong to the User
     *
     * @return BelongsToMany
     */
    // public function benefit_individual_quotes(): BelongsToMany
    // {
    //     return $this->belongsToMany(Benefit::class, 'benefit_individual_quotes')
    //         ->using(BenefitIndividualQuote::class)
    //         ->withPivot(['description']);
    // }

    /**
     * Funciones para la ejecucion de jobs
     * para el envio de los correos de propuesta economica
     *
     * @author TuDrEnCasa
     *
     * @param  array  $details
     *                          -----------------------------------------------------------------
     * @return void
     */
    public function sendPropuestaEconomicaPlanInicial($details)
    {
        $collect = collect($details['data'][0]);

        /**
         * JOB
         */
        // SendEmailPropuestaEconomica::dispatch($details, $collect, Auth::user());
    }

    public function sendPropuestaEconomicaPlanIdeal($details)
    {
        $collect = collect($details['data']);
        $group_collect = $collect->groupBy('age_range');

        /**
         * JOB
         */
        // SendEmailPropuestaEconomicaPlanIdeal::dispatch($details, $group_collect, Auth::user());
    }

    public function sendPropuestaEconomicaPlanEspecial($details)
    {
        $collect = collect($details['data']);
        $group_collect = $collect->groupBy('age_range');

        /**
         * JOB
         */
        // SendEmailPropuestaEconomicaPlanEspecial::dispatch($details, $group_collect, Auth::user());
    }

    public function sendPropuestaEconomicaMultiple($collect_final)
    {

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

            // SendEmailPropuestaEconomicaMultiple::dispatch($collect_final, $details_generals, Auth::user());
            // code...
        } catch (\Throwable $th) {
            dd($th);
        }
    }
    /* ------------------------------------------------------------------- */

    // hasMany
    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class);
    }

    public function individualQuoteObservations(): HasMany
    {
        return $this->hasMany(IndividualQuoteObservation::class)->orderByDesc('created_at');
    }
}
