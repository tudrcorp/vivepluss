<?php

declare(strict_types=1);

namespace App\Support\WhiteCompanies;

use App\Models\Commission;
use App\Models\PaidMembership;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

/**
 * Precio de venta / neta anuales pactados con Integracorp para una afiliación
 * de empresa aliada, y lo que eso implica por cuota (según la frecuencia de
 * pago) y como comisión de agencia master (precio de venta - neta).
 */
final readonly class WhiteCompanyPaymentSettlement
{
    public function __construct(
        public float $annualSalePrice,
        public float $annualNeta,
        public string $paymentFrequency,
        public int $whiteCompanyId,
        public ?int $feeId = null,
    ) {}

    /**
     * Suma precio de venta y neta de cada persona (titular/afiliados) y congela
     * el total anual.
     *
     * @param  list<array{sale_price: float, neta: float, fee_id: int}>  $lines
     */
    public static function fromPersonLines(array $lines, string $paymentFrequency, int $whiteCompanyId): self
    {
        $salePrice = 0.0;
        $neta = 0.0;
        $feeIds = [];

        foreach ($lines as $line) {
            $salePrice += $line['sale_price'];
            $neta += $line['neta'];
            $feeIds[] = $line['fee_id'];
        }

        $uniqueFeeIds = array_values(array_unique($feeIds));

        return new self(
            annualSalePrice: round($salePrice, 2),
            annualNeta: round($neta, 2),
            paymentFrequency: $paymentFrequency,
            whiteCompanyId: $whiteCompanyId,
            feeId: count($uniqueFeeIds) === 1 ? $uniqueFeeIds[0] : null,
        );
    }

    public function periods(): int
    {
        return match (mb_strtoupper(trim($this->paymentFrequency))) {
            'ANUAL' => 1,
            'SEMESTRAL' => 2,
            'TRIMESTRAL' => 4,
            'MENSUAL' => 12,
            default => 1,
        };
    }

    public function installmentNeta(): float
    {
        return round($this->annualNeta / $this->periods(), 2);
    }

    public function annualMargin(): float
    {
        return round($this->annualSalePrice - $this->annualNeta, 2);
    }

    public function installmentMasterCommission(): float
    {
        return round($this->annualMargin() / $this->periods(), 2);
    }

    /**
     * Registra la comisión de Integracorp como agencia master por esta venta a
     * empresa aliada: sin reparto por agente/sub-agente/agencia general, toda
     * la comisión es el margen (precio de venta - neta) de la cuota.
     */
    public function storeCommission(Sale $sale, PaidMembership $paidMembership): Commission
    {
        $commission = new Commission;
        $commission->code = $sale->invoice_number;
        $commission->sale_id = $sale->id;
        $commission->plan_id = $paidMembership->plan_id;
        $commission->coverage_id = $paidMembership->coverage_id;
        $commission->agent_id = $paidMembership->agent_id;
        $commission->code_agency = $paidMembership->code_agency;
        $commission->payment_frequency = $paidMembership->payment_frequency;
        $commission->affiliate_full_name = $sale->affiliate_full_name;
        $commission->pay_amount_usd = $paidMembership->pay_amount_usd;
        $commission->pay_amount_ves = $paidMembership->pay_amount_ves;
        $commission->amount = $this->installmentNeta();
        $commission->commission_agent_usd = 0;
        $commission->commission_agent_ves = 0;
        $commission->porcent_agente = 0;
        $commission->porcent_sub_agente = 0;
        $commission->commission_sub_agent_usd = 0;
        $commission->commission_sub_agent_ves = 0;
        $commission->porcent_agency_general = 0;
        $commission->commission_agency_general_usd = 0;
        $commission->commission_agency_general_ves = 0;
        $commission->porcent_agency_master = 0;
        $commission->commission_agency_master_usd = $this->installmentMasterCommission();
        $commission->commission_agency_master_ves = 0;
        $commission->payment_method = $sale->payment_method;
        $commission->affiliation_code = $sale->affiliation_code;
        $commission->created_by = Auth::user()?->name;
        $commission->save();

        return $commission;
    }
}
