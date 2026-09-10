<?php

namespace App\Models;

use App\Support\CorporateQuoteResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliationCorporate extends Model
{
    protected $table = 'affiliation_corporates';

    protected $fillable = [
        'code',
        'corporate_quote_id',
        'code_agency',
        'agent_id',
        'owner_code',

        'name_corporate',
        'rif',
        'address',
        'city_id',
        'state_id',
        'country_id',
        'region_id',
        'phone',
        'email',
        'full_name_contact',
        'nro_identificacion_contact',
        'phone_contact',
        'email_contact',
        'created_by',
        'status',
        'document',
        'observations',
        'payment_frequency',
        'fee_anual',
        'total_amount',
        'vaucher_ils',
        'date_payment_initial_ils',
        'date_payment_final_ils',
        'document_ils',
        'type',
        'poblation',
        'activated_at',

        // ...Unidad de Negocio y linea de servicio
        'business_unit_id',
        'business_line_id',
        'ownerAccountManagers',

        // PROVEEDORRES DE SERVICIOS
        'service_providers',

        // ...Fecha de Vigencia de la afiliacion
        'effective_date',

        // Unidades e Negocio y Lineas de Servicio
        'business_unit_id',
        'business_line_id',
    ];

    protected $casts = [
        'service_providers' => 'array',
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

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function corporateAffiliates()
    {
        return $this->hasMany(AffiliateCorporate::class);
    }

    public function coverage()
    {
        return $this->belongsTo(Coverage::class);
    }

    /**
     * Ya no es una relación belongsTo estándar: corporate_quote_id puede
     * apuntar a la tabla legacy de Integracorp (conexión mysql) o a la
     * propia de ViVEplus (mysql_vivepluss), según el rango de id. Ver
     * App\Support\CorporateQuoteResolver.
     */
    public function getCorporateQuoteAttribute(): CorporateQuote|IntegracorpCorporateQuote|null
    {
        return CorporateQuoteResolver::find($this->corporate_quote_id);
    }

    public function paid_membership_corporates()
    {
        return $this->hasMany(PaidMembershipCorporate::class);
    }

    public function status_log_corporate_affiliations()
    {
        return $this->hasMany(StatusLogAffiliationCorporate::class);
    }

    public function affiliationCorporateObservations(): HasMany
    {
        return $this->hasMany(AffiliationCorporateObservation::class)->orderByDesc('created_at');
    }

    public function affiliationCorporatePlans(): HasMany
    {
        return $this->hasMany(AfilliationCorporatePlan::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'code_agency', 'code');
    }
}
