<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditReconciliation extends Model
{
    protected $table = 'credit_reconciliations';

    protected $fillable = [
        'entity_type',
        'white_company_id',
        'agency_id',
        'agent_id',
        'paid_membership_id',
        'paid_membership_corporate_id',
        'collection_id',
        'affiliation_kind',
        'affiliation_id',
        'affiliation_corporate_id',
        'affiliation_code',
        'affiliation_information',
        'affiliates_count',
        'annual_amount',
        'total_to_pay',
        'payment_frequency',
        'collection_invoice_number',
        'plan_id',
        'plan_type',
        'created_by',
        'updated_by',
    ];

    public function whiteCompany()
    {
        return $this->belongsTo(WhiteCompany::class);
    }

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class);
    }

    public function paidMembership()
    {
        return $this->belongsTo(PaidMembership::class);
    }

    /**
     * Crédito de la marca blanca ($assigned_credit) menos todo lo ya
     * consumido a través de movimientos de crédito registrados aquí.
     */
    public static function remainingCredit(int|string|null $whiteCompanyId): float
    {
        if (blank($whiteCompanyId)) {
            return 0.0;
        }

        $assigned = (float) (WhiteCompany::find($whiteCompanyId)?->assigned_credit ?? 0);
        $used = (float) static::where('white_company_id', $whiteCompanyId)->sum('total_to_pay');

        return $assigned - $used;
    }
}
