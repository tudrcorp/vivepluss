<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $fillable = [
        'code',
        'sale_id',
        'plan_id',
        'coverage_id',
        'agent_id',
        'code_agency',
        'payment_frequency',
        'affiliate_full_name',
        'pay_amount_usd',
        'pay_amount_ves',
        'amount',
        'porcent_agente',
        'commission_agent_usd',
        'commission_agent_ves',
        'porcent_sub_agente',
        'commission_sub_agent_usd',
        'commission_sub_agent_ves',
        'porcent_agency_general',
        'commission_agency_general_usd',
        'commission_agency_general_ves',
        'porcent_agency_master',
        'commission_agency_master_usd',
        'commission_agency_master_ves',
        'payment_method',
        'affiliation_code',
        'created_by',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'code_agency', 'code');
    }
}
