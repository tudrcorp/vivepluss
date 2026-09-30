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
     * Cupo que Integracorp cargó en white_companies.assigned_credit.
     * Cero (o marca blanca inexistente) significa que no hay tope configurado.
     */
    public static function assignedCredit(int|string|null $whiteCompanyId): float
    {
        if (blank($whiteCompanyId)) {
            return 0.0;
        }

        return (float) (WhiteCompany::find($whiteCompanyId)?->assigned_credit ?? 0);
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

        $used = (float) static::where('white_company_id', $whiteCompanyId)->sum('total_to_pay');

        return static::assignedCredit($whiteCompanyId) - $used;
    }

    /**
     * Si Integracorp no asignó cupo (assigned_credit = 0), no hay tope y el
     * pago a crédito no se bloquea. El tope solo aplica cuando el cupo es
     * mayor que cero: entonces el monto no puede superar el saldo restante.
     */
    public static function canCoverPayment(int|string|null $whiteCompanyId, float $amount): bool
    {
        if (static::assignedCredit($whiteCompanyId) <= 0) {
            return true;
        }

        return $amount <= static::remainingCredit($whiteCompanyId);
    }
}
