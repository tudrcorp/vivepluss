<?php

namespace App\Http\Controllers;

use App\Jobs\SendAffiliationDocumentWhatsApp;
use App\Mail\AffiliationAutoActivatedMail;
use App\Mail\PaymentProofUploadedMail;
use App\Models\Affiliate;
use App\Models\Affiliation;
use App\Models\Collection;
use App\Models\Configuration;
use App\Models\CreditReconciliation;
use App\Models\PaidMembership;
use App\Models\Sale;
use App\Models\WhiteCompany;
use App\Support\WhiteCompanies\WhiteCompanyNegotiatedRateResolver;
use App\Support\WhiteCompanies\WhiteCompanyPaymentSettlement;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AffiliationController extends Controller
{
    public static function uploadPayment($record, $data, $type_roll)
    {

        try {
            // dd($data, $record);
            // $validate = self::getValidation($record, $data);

            // 1. Actualizamos la tabla de afiliaciones
            $record->update([
                'family_members' => Affiliate::select('affiliation_id')->where('affiliation_id', $record->id)->count(),
            ]);

            if ($record['payment_frequency'] == 'ANUAL') {

                /** PAGO USD */
                if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'pay_amount_usd' => $data['total_amount'],
                        'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                        'document_usd' => $data['document_usd'],
                        'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => 'N/A',
                        'payment_method_ves' => 'N/A',
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,

                    ]);
                }

                /** PAGO BSD */
                if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_ves' => $data['document_ves'],
                        'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => 'N/A',
                        'payment_method_ves' => 'N/A',
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => $data['bank_ves'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }

                /** PAGO MULTIPLE */
                if ($data['payment_method'] == 'MULTIPLE') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => $data['pay_amount_usd'],
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_usd' => $data['document_usd'],
                        'document_ves' => $data['document_ves'],
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => $data['payment_method_usd'],
                        'payment_method_ves' => $data['payment_method_ves'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                        'bank_ves' => $data['bank_ves'] ?? 'N/A',
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                    ]);
                }
            }

            if ($record['payment_frequency'] == 'TRIMESTRAL') {

                /** PAGO USD */
                if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'total_amount' => $data['total_amount'],
                        'pay_amount_usd' => $data['total_amount'],
                        'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                        'document_usd' => $data['document_usd'],
                        'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                    ]);
                }

                /** PAGO BSD */
                if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_ves' => $data['document_ves'],
                        'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => $data['bank_ves'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }

                /** PAGO MULTIPLE */
                if ($data['payment_method'] == 'MULTIPLE') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => $data['pay_amount_usd'],
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_usd' => $data['document_usd'],
                        'document_ves' => $data['document_ves'],
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => $data['payment_method_usd'],
                        'payment_method_ves' => $data['payment_method_ves'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                        'bank_ves' => $data['bank_ves'] ?? 'N/A',
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                    ]);
                }
            }

            if ($record['payment_frequency'] == 'SEMESTRAL') {

                /** PAGO USD */
                if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'pay_amount_usd' => $data['total_amount'],
                        'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                        'document_usd' => $data['document_usd'],
                        'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }

                /** PAGO BSD */
                if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_ves' => $data['document_ves'],
                        'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => $data['bank_ves'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }

                /** PAGO MULTIPLE */
                if ($data['payment_method'] == 'MULTIPLE') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => $data['pay_amount_usd'],
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_usd' => $data['document_usd'],
                        'document_ves' => $data['document_ves'],
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => $data['payment_method_usd'],
                        'payment_method_ves' => $data['payment_method_ves'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                        'bank_ves' => $data['bank_ves'] ?? 'N/A',
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }
            }

            if ($record['payment_frequency'] == 'MENSUAL') {

                /** PAGO USD */
                if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'pay_amount_usd' => $data['total_amount'],
                        'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                        'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                        'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }

                /** PAGO BSD */
                if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_ves' => $data['document_ves'],
                        'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                        'payment_method' => $data['payment_method'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                        'bank_ves' => $data['bank_ves'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }

                /** PAGO MULTIPLE */
                if ($data['payment_method'] == 'MULTIPLE') {

                    $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        'total_amount' => $data['total_amount'],
                        'tasa_bcv' => $data['tasa_bcv'],
                        'pay_amount_usd' => $data['pay_amount_usd'],
                        'pay_amount_ves' => $data['pay_amount_ves'],
                        'document_usd' => $data['document_usd'] == null ? 'N/A' : $data['document_usd'],
                        'document_ves' => $data['document_ves'],
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => $data['payment_method_usd'],
                        'payment_method_ves' => $data['payment_method_ves'],
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                        'reference_payment_ves' => $data['reference_payment_ves'],
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                        'bank_ves' => $data['bank_ves'] ?? 'N/A',
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                    ]);
                }
            }

            /**
             * PAGO A CRÉDITO: no hay comprobante real que subir. Se genera una nota de
             * crédito (PDF) que documenta el movimiento, se adjunta como comprobante y
             * el pago queda aprobado de inmediato (ver bloque de aprobación más abajo).
             * No depende de $record['payment_frequency'] porque el monto/fecha no varían
             * por ella (igual que el resto de los métodos de pago arriba).
             */
            if ($data['payment_method'] == 'CREDITO') {
                /**
                 * Se resuelve (y bloquea si falta) la tarifa negociada con Integracorp
                 * ANTES de escribir nada, para que "falta tarifa negociada" no deje la
                 * afiliación a medio aprobar (nota de crédito, comprobante, ledger).
                 */
                $settlement = (new WhiteCompanyNegotiatedRateResolver)->settlementForAffiliation($record);

                $noteNumber = 'NC-'.$record->code.'-'.now()->format('YmdHis');
                $remainingCreditBefore = CreditReconciliation::remainingCredit($record->white_company_id);

                $documentPath = self::generateCreditNote($record, $data, $remainingCreditBefore, $noteNumber);

                $paidMembership = $record->paid_memberships()->create([
                    'affiliation_id' => $record->id,
                    'agent_id' => $record->agent_id,
                    'code_agency' => $record->code_agency,
                    'plan_id' => $record->plan_id,
                    'coverage_id' => $record->coverage_id,
                    'total_amount' => $data['total_amount'],
                    'pay_amount_usd' => $data['total_amount'],
                    'pay_amount_ves' => 0.00,
                    'document_usd' => 'N/A',
                    'document_ves' => $documentPath,
                    'payment_method' => $data['payment_method'],
                    'payment_method_usd' => 'N/A',
                    'payment_method_ves' => 'N/A',
                    'payment_frequency' => $record['payment_frequency'],
                    'payment_date' => now()->format('d-m-Y'),
                    'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                    'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                    'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                    'created_by' => Auth::user()->name,
                    'type_roll' => $type_roll,
                    'status' => 'APROBADO',
                    'invoice_number' => $noteNumber,
                ]);

                self::recordCreditMovement($record, $paidMembership, (float) $data['total_amount'], $noteNumber);
            }

            /**
             * El comprobante recién creado debe viajar con el white_company_id de la
             * afiliación (marca blanca). Se actualiza aquí en vez de en cada uno de los
             * create() de arriba para no tener que tocar los 12 bloques por método/frecuencia.
             * Si además el analista seleccionó a qué cuota(s) pendiente(s) pertenece este
             * comprobante, se marcan como pagadas (con su propio white_company_id) y se
             * enlazan al comprobante a través de invoice_number (no existe FK entre ambas tablas).
             * ----------------------------------------------------------------------------------------------------
             */
            $paidMembershipUpdate = [
                'white_company_id' => $record->white_company_id,
            ];

            if (! empty($data['collections'] ?? [])) {
                $paidCollections = Collection::whereIn('id', $data['collections'])->get();

                $paidCollections->each(fn (Collection $collection) => $collection->update([
                    'status' => 'PAGADO',
                    'white_company_id' => $record->white_company_id,
                ]));

                $paidMembershipUpdate['invoice_number'] = $paidCollections->pluck('collection_invoice_number')->implode(', ');
            }

            $paidMembership = $record->paid_memberships()->latest()->first();

            if ($paidMembership) {
                $paidMembershipUpdate['document_usd'] = self::publicDocumentUrl($paidMembership->document_usd);
                $paidMembershipUpdate['document_ves'] = self::publicDocumentUrl($paidMembership->document_ves);
            }

            $paidMembership?->update($paidMembershipUpdate);

            self::notifyPaymentProofUploaded($record, $paidMembership);

            /**
             * Solo el pago a crédito se aprueba y activa de forma automática (sin paso
             * de aprobación manual), con su correo a administración/afiliaciones/negocios.
             * Cualquier otro método de pago (incluido el primer comprobante de la
             * afiliación) queda en PENDIENTE: la validación del comprobante, la
             * aprobación, la generación de las próximas cuotas y la activación de la
             * afiliación las realiza el equipo de Integracorp, no ViVEplus.
             */
            if ($data['payment_method'] == 'CREDITO') {
                self::approveAndActivate($record, $data, $settlement);
            }

            return true;

            // code...
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            Notification::make()
                ->title('EXCEPTION')
                ->body($th->getMessage())
                ->danger()
                ->send();
            // throw $th;
        }
    }

    /**
     * Aprueba el comprobante recién cargado y activa la afiliación (si no lo estaba ya).
     * Solo se invoca para pagos a CREDITO (ver uploadPayment()/uploadPaymentMultipleAffiliations()):
     * el resto de los métodos de pago quedan en PENDIENTE a la espera de que Integracorp
     * los valide/apruebe. Notifica el resultado por correo; un fallo de envío no debe
     * revertir la activación.
     */
    private static function approveAndActivate(Affiliation $record, array $data, WhiteCompanyPaymentSettlement $settlement): void
    {
        $paidMembership = $record->paid_memberships()->latest()->first();
        $paidMembership?->update(['status' => 'APROBADO']);

        /**
         * Con pagos a crédito este método puede invocarse en cuotas posteriores a la
         * activación (no solo en el primer pago), así que la generación de cuotas
         * futuras, el registro de venta/comisión y el correo de activación solo deben
         * dispararse la primera vez que la afiliación pasa a ACTIVA, no en cada
         * aprobación de pago subsiguiente (igual que hace Integracorp: venta y
         * comisión se registran una sola vez, en el primer pago).
         */
        if ($record->activated_at !== null) {
            return;
        }

        $record->activated_at = now()->format('d/m/Y');
        $record->effective_date = Carbon::createFromFormat('d/m/Y', now()->format('d/m/Y'))->addYear()->format('d/m/Y');
        $record->status = 'ACTIVA';
        $record->save();

        self::createUpcomingCollections($record, $paidMembership);

        if ($paidMembership instanceof PaidMembership) {
            self::registerSaleAndCommission($record, $paidMembership, $settlement);
        }

        try {
            $recipients = app()->environment('production')
                ? config('parametros.ACTIVATION_NOTIFICATION_EMAILS')
                : config('parametros.ACTIVATION_NOTIFICATION_EMAILS_DEV');

            if (empty($recipients)) {
                return;
            }

            $configuration = Configuration::where('white_company_id', $record->white_company_id)->first();

            Mail::to($recipients)->send(new AffiliationAutoActivatedMail(
                $record->fresh(['plan', 'coverage', 'agency', 'affiliates']),
                $configuration?->white_company_name ?? 'N/A',
                self::resolveLogoPath($configuration),
                self::resolvePaymentDocumentPaths($paidMembership),
                $paidMembership,
                // reference_payment_zelle no está en el $fillable de PaidMembership (bug preexistente),
                // así que no queda persistida; se toma del $data crudo del formulario como respaldo.
                $data['reference_payment_zelle'] ?? null,
                $configuration?->primaryColor ?: '#A13DDB',
            ));
        } catch (\Throwable $th) {
            Log::error('No se pudo enviar el correo de activación automática de la afiliación '.$record->code.': '.$th->getMessage());
        }
    }

    /**
     * Registra en las tablas compartidas con Integracorp (sales/commissions) la
     * venta y la comisión de esta afiliación de empresa aliada, igual que hace
     * Integracorp para sus propias aprobaciones: el total de la venta y la
     * comisión de agencia master salen de la tarifa negociada (neta/margen),
     * no del monto que el analista cargó en el comprobante.
     */
    private static function registerSaleAndCommission(
        Affiliation $record,
        PaidMembership $paidMembership,
        WhiteCompanyPaymentSettlement $settlement,
    ): void {
        DB::transaction(function () use ($record, $paidMembership, $settlement) {
            $lastInvoiceNumber = Sale::query()->latest('id')->value('invoice_number');

            $sale = Sale::create([
                'date_activation' => $record->activated_at,
                'owner_code' => $record->owner_code,
                'code_agency' => $record->code_agency,
                'plan_id' => $record->plan_id,
                'coverage_id' => $record->coverage_id,
                'agent_id' => $record->agent_id,
                'invoice_number' => UtilsController::generateCorrelativeSale($lastInvoiceNumber ?? (now()->format('m').'-00000')),
                'affiliation_code' => $record->code,
                'affiliate_full_name' => $record->full_name_ti,
                'affiliate_contact' => $record->full_name_payer,
                'affiliate_ci_rif' => $record->nro_identificacion_ti,
                'affiliate_phone' => $record->phone_ti,
                'affiliate_email' => $record->email_ti,
                'service' => 'servicio',
                'persons' => $record->family_members,
                'total_amount' => $settlement->installmentNeta(),
                'type' => 'AFILIACION INDIVIDUAL',
                'payment_method' => 'CREDITO',
                'payment_frequency' => $record->payment_frequency,
                'created_by' => Auth::user()->name,
                'pay_amount_usd' => $paidMembership->pay_amount_usd,
                'pay_amount_ves' => $paidMembership->pay_amount_ves,
                'type_roll' => $paidMembership->type_roll,
                'payment_date' => $paidMembership->payment_date,
                'white_company_id' => $record->white_company_id,
            ]);

            $settlement->storeCommission($sale, $paidMembership);
        });
    }

    /**
     * El logo de marca blanca se sube con FileUpload::make('brandLogo') sin declarar
     * disco explícito, así que según la config puede terminar en 'public' o en 'local'
     * (mismo tipo de inconsistencia que document_usd/document_ves). Se prueban ambos
     * y se devuelve null si el archivo no existe en ninguno (evita el ícono de imagen rota).
     */
    private static function resolveLogoPath(?Configuration $configuration): ?string
    {
        if (blank($configuration?->brandLogo)) {
            return null;
        }

        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($configuration->brandLogo)) {
                return Storage::disk($disk)->path($configuration->brandLogo);
            }
        }

        return null;
    }

    /**
     * Genera en "collections" las próximas cuotas pendientes de la afiliación según
     * su frecuencia de pago, a partir de la fecha de activación (este primer pago ya
     * cubrió el período inicial, por eso el conteo empieza en el siguiente período):
     * ANUAL → 1 cuota a los 12 meses (recordatorio de renovación).
     * SEMESTRAL → 1 cuota a los 6 meses.
     * TRIMESTRAL → 3 cuotas a los 3, 6 y 9 meses.
     * MENSUAL → 11 cuotas, una por cada mes restante del ciclo anual.
     * Quedan en status 'POR PAGAR' (default de la columna) hasta que se paguen.
     */
    private static function createUpcomingCollections(Affiliation $record, ?PaidMembership $paidMembership): void
    {
        $monthsAhead = match ($record->payment_frequency) {
            'ANUAL' => [12],
            'SEMESTRAL' => [6],
            'TRIMESTRAL' => [3, 6, 9],
            'MENSUAL' => range(1, 11),
            default => [],
        };

        if (empty($monthsAhead)) {
            return;
        }

        $activatedAt = Carbon::createFromFormat('d/m/Y', $record->activated_at);
        $invoiceNumber = Collection::query()->latest('id')->value('collection_invoice_number') ?? (date('m').'-00000');
        $quoteNumber = $record->code_individual_quote ?: ($record->individual_quote->code ?? '');

        foreach ($monthsAhead as $months) {
            $invoiceNumber = UtilsController::generateCorrelativeCollection($invoiceNumber);
            $nextPaymentDate = $activatedAt->copy()->addMonthsNoOverflow($months);

            Collection::create([
                'include_date' => $record->activated_at,
                'owner_code' => $record->owner_code,
                'code_agency' => $record->code_agency,
                'agent_id' => $record->agent_id,
                'collection_invoice_number' => $invoiceNumber,
                'quote_number' => $quoteNumber,
                'affiliation_code' => $record->code,
                'affiliate_full_name' => $record->full_name_ti,
                'affiliate_contact' => $record->full_name_payer,
                'affiliate_ci_rif' => $record->nro_identificacion_ti,
                'affiliate_phone' => $record->phone_ti,
                'affiliate_email' => $record->email_ti,
                'affiliate_status' => $record->status,
                'plan_id' => $record->plan_id,
                'coverage_id' => $record->coverage_id,
                'service' => 'servicio',
                'persons' => (string) $record->family_members,
                'type' => 'AFILIACION INDIVIDUAL',
                'payment_method' => $paidMembership?->payment_method,
                'payment_frequency' => $record->payment_frequency,
                'next_payment_date' => $nextPaymentDate->format('d/m/Y'),
                'total_amount' => $record->total_amount,
                'expiration_date' => $nextPaymentDate->format('d/m/Y'),
                'status' => 'POR PAGAR',
                'days' => 0,
                'created_by' => Auth::user()->name,
                'pay_amount_usd' => 0.00,
                'pay_amount_ves' => 0.00,
                'bank_usd' => 'N/A',
                'bank_ves' => 'N/A',
                'filter_next_payment_date' => $nextPaymentDate->format('Y-m-d'),
                'white_company_id' => $record->white_company_id,
            ]);
        }
    }

    /**
     * Convierte la ruta relativa con la que Filament guarda un comprobante en la
     * URL pública absoluta de ViVEplus, con el mismo criterio que generateCreditNote():
     * Integracorp comparte esta base de datos pero no el almacenamiento, así que una
     * ruta relativa la interpretaría como archivo propio y daría 404 al abrirla desde
     * su panel. Con la URL absoluta sus analistas descargan el comprobante igual que
     * ya hacen con la nota de crédito.
     *
     * Solo aplica a lo que se carga de aquí en adelante: los comprobantes anteriores
     * conservan su ruta relativa (no se hizo backfill). Un valor vacío, 'N/A' o que ya
     * sea una URL se devuelve intacto, y si el archivo no aparece en el disco 'public'
     * -por ejemplo un registro viejo que quedó en 'local'- se deja como está en vez de
     * fabricar un enlace roto.
     */
    public static function publicDocumentUrl(?string $value): ?string
    {
        if (blank($value) || $value === 'N/A' || Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (! Storage::disk('public')->exists($value)) {
            Log::warning('No se pudo publicar la URL del comprobante de pago: el archivo no está en el disco public.', [
                'path' => $value,
            ]);

            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    /**
     * El comprobante de pago se guarda en document_usd y/o document_ves (según el
     * método de pago puede venir uno o ambos), hoy siempre en el disco 'public' y
     * como URL absoluta -ver publicDocumentUrl()-. Aquí se revierten a rutas de
     * disco para adjuntarlos al correo/WhatsApp; los registros anteriores al cambio
     * siguen siendo rutas relativas, y las de document_usd pueden estar en 'local'.
     *
     * @return array<int, string>
     */
    public static function resolvePaymentDocumentPaths(?PaidMembership $paidMembership): array
    {
        if (! $paidMembership) {
            return [];
        }

        $paths = [];

        foreach (['document_usd' => 'local', 'document_ves' => 'public'] as $field => $disk) {
            $value = $paidMembership->{$field};

            if (blank($value) || $value === 'N/A') {
                continue;
            }

            // Las notas de crédito (pagos a CREDITO) se guardan como URL absoluta
            // en vez de ruta relativa de disco, para que Integracorp -que comparte
            // esta misma base de datos pero corre en un dominio/servidor aparte,
            // sin almacenamiento compartido- también pueda enlazarlas sin
            // confundirlas con un archivo propio. Aquí se revierte a ruta relativa
            // para poder leer el archivo local y adjuntarlo al correo.
            if (Str::startsWith($value, ['http://', 'https://'])) {
                $localPath = Str::after($value, '/storage/');

                if (Storage::disk('public')->exists($localPath)) {
                    $paths[] = Storage::disk('public')->path($localPath);
                }

                continue;
            }

            if (Storage::disk($disk)->exists($value)) {
                $paths[] = Storage::disk($disk)->path($value);
            }
        }

        return $paths;
    }

    /**
     * Avisa por correo y WhatsApp, a los contactos que Integracorp le indicó a
     * ViVEplus (configurados en Configuration::payment_notification_emails/phones
     * por marca blanca), que se cargó un comprobante de pago -para cualquier
     * método, en cada carga, no solo la primera vez ni solo para CREDITO. El
     * adjunto sale de resolvePaymentDocumentPaths(), que ya devuelve la nota de
     * crédito cuando el método es CREDITO (se guarda como document_ves en
     * generateCreditNote()), así que no hace falta ninguna rama especial aquí.
     * Un fallo de envío solo se loggea: nunca debe revertir ni bloquear la carga
     * del comprobante ya persistida.
     */
    private static function notifyPaymentProofUploaded(Affiliation $record, ?PaidMembership $paidMembership): void
    {
        if (! $paidMembership) {
            return;
        }

        try {
            $configuration = Configuration::where('white_company_id', $record->white_company_id)->first();

            if (! $configuration || ! $configuration->payment_notifications_enabled) {
                return;
            }

            $emails = $configuration->payment_notification_emails ?? [];
            $phones = $configuration->payment_notification_phones ?? [];

            if (blank($emails) && blank($phones)) {
                return;
            }

            $documentPaths = self::resolvePaymentDocumentPaths($paidMembership);

            if (filled($emails)) {
                Mail::to($emails)->queue(new PaymentProofUploadedMail(
                    $record,
                    $paidMembership,
                    $configuration->white_company_name ?? 'N/A',
                ));
            }

            if (filled($phones)) {
                $isCredito = $paidMembership->payment_method === 'CREDITO';
                $label = $isCredito ? 'Nota de crédito' : 'Comprobante de pago';
                $body = "📄 {$label} cargado — Afiliación {$record->code}\n\n"
                    ."Método: {$paidMembership->payment_method}\n"
                    ."Monto: {$paidMembership->total_amount}\n\n"
                    .'Detalle disponible en el panel de ViVEplus.';

                foreach ($documentPaths as $path) {
                    foreach ($phones as $phone) {
                        SendAffiliationDocumentWhatsApp::dispatch($phone, $body, $path, basename($path));
                    }
                }

                if (empty($documentPaths)) {
                    foreach ($phones as $phone) {
                        SendAffiliationDocumentWhatsApp::dispatch($phone, $body);
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::error('No se pudo enviar la notificación de comprobante de pago de la afiliación '.$record->code.': '.$th->getMessage());
        }
    }

    /**
     * Genera el PDF de la "nota de crédito" de un pago a crédito y lo guarda en el
     * disco 'public' (mismo disco que ya usa document_ves), devolviendo la URL
     * pública absoluta (no una ruta relativa) a guardar en el paid_membership como
     * comprobante adjunto: Integracorp comparte la base de datos con ViVEplus pero
     * no su almacenamiento de archivos, así que una ruta relativa se interpretaría
     * ahí como "propia" y resultaría en un 404. Con una URL absoluta, cualquier
     * sistema que la enlace tal cual (sin anteponerle su propio dominio) apunta al
     * PDF real servido por ViVEplus.
     */
    private static function generateCreditNote(Affiliation $record, array $data, float $remainingCreditBefore, string $noteNumber): string
    {
        $configuration = Configuration::where('white_company_id', $record->white_company_id)->first();
        $whiteCompany = WhiteCompany::find($record->white_company_id);

        $relativePath = 'notas-credito/'.$noteNumber.'.pdf';

        ini_set('memory_limit', '512M');

        $pdf = Pdf::loadView('documents.nota-credito', [
            'whiteCompanyName' => $configuration?->white_company_name ?? ($whiteCompany?->name ?? 'N/A'),
            'logoPath' => self::resolveLogoPath($configuration),
            'primaryColor' => $configuration?->primaryColor ?? '#1f2937',
            'currency' => $configuration?->currency_symbol ?? 'EUR€',
            'coverageCurrency' => Configuration::coverageCurrencySymbol(),
            'noteNumber' => $noteNumber,
            'date' => now()->format('d/m/Y'),
            'affiliationCode' => $record->code,
            'affiliateName' => $record->full_name_ti,
            'planDescription' => $record->plan?->description ?? 'N/A',
            'coverage' => $record->coverage_id ? $record->coverage?->price : null,
            'paymentFrequency' => $record->payment_frequency,
            'assignedCredit' => (float) ($whiteCompany?->assigned_credit ?? 0),
            'remainingCreditBefore' => $remainingCreditBefore,
            'amount' => (float) $data['total_amount'],
        ]);

        Storage::disk('public')->makeDirectory('notas-credito');
        $pdf->save(Storage::disk('public')->path($relativePath));

        return Storage::disk('public')->url($relativePath);
    }

    /**
     * Registra en el ledger de crédito (credit_reconciliations) el movimiento
     * generado por un pago a crédito, para que remainingCredit() lo descuente.
     */
    private static function recordCreditMovement(Affiliation $record, PaidMembership $paidMembership, float $amount, string $noteNumber): void
    {
        CreditReconciliation::create([
            'entity_type' => 'white_company',
            'white_company_id' => $record->white_company_id,
            'agent_id' => $record->agent_id,
            'paid_membership_id' => $paidMembership->id,
            'affiliation_kind' => 'individual',
            'affiliation_id' => $record->id,
            'affiliation_code' => $record->code,
            'affiliation_information' => $record->full_name_ti,
            'affiliates_count' => $record->family_members ?: 1,
            'annual_amount' => $record->total_amount,
            'total_to_pay' => $amount,
            'payment_frequency' => $record->payment_frequency,
            'collection_invoice_number' => $noteNumber,
            'plan_id' => $record->plan_id,
            'plan_type' => 'INDIVIDUAL',
            'created_by' => Auth::user()->name,
        ]);
    }

    public static function uploadPaymentMultipleAffiliations($records, $data, $type_roll)
    {

        try {

            foreach ($records as $record) {
                $record->update([
                    'family_members' => Affiliate::select('affiliation_id')->where('affiliation_id', $record->id)->count(),
                ]);

                if ($record['payment_frequency'] == 'ANUAL') {

                    /** PAGO USD */
                    if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $data['total_amount'],
                            'pay_amount_usd' => $data['total_amount'],
                            'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                            'document_usd' => $data['document_usd'],
                            'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'payment_method_usd' => 'N/A',
                            'payment_method_ves' => 'N/A',
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,

                        ]);
                    }

                    /** PAGO BSD */
                    if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'total_amount' => $data['total_amount'],
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_ves' => $data['document_ves'],
                            'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'payment_method_usd' => 'N/A',
                            'payment_method_ves' => 'N/A',
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => $data['bank_ves'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }

                    /** PAGO MULTIPLE */
                    if ($data['payment_method'] == 'MULTIPLE') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'total_amount' => $data['total_amount'],
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => $data['pay_amount_usd'],
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_usd' => $data['document_usd'],
                            'document_ves' => $data['document_ves'],
                            'payment_method' => $data['payment_method'],
                            'payment_method_usd' => $data['payment_method_usd'],
                            'payment_method_ves' => $data['payment_method_ves'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                            'bank_ves' => $data['bank_ves'] ?? 'N/A',
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        ]);
                    }
                }

                if ($record['payment_frequency'] == 'TRIMESTRAL') {

                    /** PAGO USD */
                    if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'total_amount' => $record->total_amount,
                            'pay_amount_usd' => $data['total_amount'],
                            'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                            'document_usd' => $data['document_usd'],
                            'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        ]);
                    }

                    /** PAGO BSD */
                    if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'total_amount' => $record->total_amount,
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_ves' => $data['document_ves'],
                            'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => $data['bank_ves'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }

                    /** PAGO MULTIPLE */
                    if ($data['payment_method'] == 'MULTIPLE') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'total_amount' => $record->total_amount,
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => $data['pay_amount_usd'],
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_usd' => $data['document_usd'],
                            'document_ves' => $data['document_ves'],
                            'payment_method' => $data['payment_method'],
                            'payment_method_usd' => $data['payment_method_usd'],
                            'payment_method_ves' => $data['payment_method_ves'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                            'bank_ves' => $data['bank_ves'] ?? 'N/A',
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                        ]);
                    }
                }

                if ($record['payment_frequency'] == 'SEMESTRAL') {

                    /** PAGO USD */
                    if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $record->total_amount,
                            'pay_amount_usd' => $data['total_amount'],
                            'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                            'document_usd' => $data['document_usd'],
                            'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }

                    /** PAGO BSD */
                    if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => isset($data['name_ti_usd']) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $record->total_amount,
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_ves' => $data['document_ves'],
                            'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => $data['bank_ves'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }

                    /** PAGO MULTIPLE */
                    if ($data['payment_method'] == 'MULTIPLE') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $record->total_amount,
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => $data['pay_amount_usd'],
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_usd' => $data['document_usd'],
                            'document_ves' => $data['document_ves'],
                            'payment_method' => $data['payment_method'],
                            'payment_method_usd' => $data['payment_method_usd'],
                            'payment_method_ves' => $data['payment_method_ves'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_zelle' => $data['reference_payment_zelle'] == null ? 'N/A' : $data['reference_payment_zelle'],
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                            'bank_ves' => $data['bank_ves'] ?? 'N/A',
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }
                }

                if ($record['payment_frequency'] == 'MENSUAL') {

                    /** PAGO USD */
                    if ($data['payment_method'] == 'EFECTIVO US$' || $data['payment_method'] == 'ZELLE' || $data['payment_method'] == 'TRANSFERENCIA US$') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $record->total_amount,
                            'pay_amount_usd' => $data['total_amount'],
                            'pay_amount_ves' => isset($data['pay_amount_ves']) ? $data['pay_amount_ves'] : 0.00,
                            'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                            'document_ves' => isset($data['document_ves']) ? $data['document_ves'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => isset($data['reference_payment_ves']) ? $data['reference_payment_ves'] : 'N/A',
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => isset($data['bank_ves']) ? $data['bank_ves'] : 'N/A',
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }

                    /** PAGO BSD */
                    if ($data['payment_method'] == 'PAGO MOVIL VES' || $data['payment_method'] == 'TRANSFERENCIA VES') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $record->total_amount,
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => isset($data['pay_amount_usd']) ? $data['pay_amount_usd'] : 0.00,
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_ves' => $data['document_ves'],
                            'document_usd' => isset($data['document_usd']) ? $data['document_usd'] : 'N/A',
                            'payment_method' => $data['payment_method'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'reference_payment_zelle' => isset($data['reference_payment_zelle']) ? $data['reference_payment_zelle'] : 'N/A',
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => isset($data['bank_usd']) ? $data['bank_usd'] : 'N/A',
                            'bank_ves' => $data['bank_ves'],
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }

                    /** PAGO MULTIPLE */
                    if ($data['payment_method'] == 'MULTIPLE') {

                        $record->paid_memberships()->create([
                            'affiliation_id' => $record->id,
                            'agent_id' => $record->agent_id,
                            'code_agency' => $record->code_agency,
                            'plan_id' => $record->plan_id,
                            'coverage_id' => $record->coverage_id,
                            'name_ti_usd' => array_key_exists('name_ti_usd', $data) ? $data['name_ti_usd'] : 'N/A',
                            'total_amount' => $record->total_amount,
                            'tasa_bcv' => $data['tasa_bcv'],
                            'pay_amount_usd' => $data['pay_amount_usd'],
                            'pay_amount_ves' => $data['pay_amount_ves'],
                            'document_usd' => $data['document_usd'] == null ? 'N/A' : $data['document_usd'],
                            'document_ves' => $data['document_ves'],
                            'payment_method' => $data['payment_method'],
                            'payment_method_usd' => $data['payment_method_usd'],
                            'payment_method_ves' => $data['payment_method_ves'],
                            'payment_frequency' => $record['payment_frequency'],
                            'payment_date' => now()->format('d-m-Y'),
                            'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'reference_payment_zelle' => array_key_exists('reference_payment_zelle', $data) ? $data['reference_payment_zelle'] : 'N/A',
                            'reference_payment_ves' => $data['reference_payment_ves'],
                            'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                            'bank_usd' => $data['bank_usd'] == null ? 'N/A' : $data['bank_usd'],
                            'bank_ves' => $data['bank_ves'] ?? 'N/A',
                            'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                            'created_by' => Auth::user()->name,
                            'type_roll' => $type_roll,
                        ]);
                    }
                }

                /**
                 * PAGO A CRÉDITO: una nota de crédito y un movimiento de ledger por
                 * cada afiliación seleccionada (usa el total_amount propio de cada
                 * una, no el total combinado del modal). Igual que en uploadPayment().
                 */
                if ($data['payment_method'] == 'CREDITO') {
                    /**
                     * Se resuelve (y bloquea si falta) la tarifa negociada con Integracorp
                     * ANTES de escribir nada, igual que en uploadPayment(), para que "falta
                     * tarifa negociada" no deje esta afiliación del lote a medio aprobar.
                     */
                    $settlement = (new WhiteCompanyNegotiatedRateResolver)->settlementForAffiliation($record);

                    $noteNumber = 'NC-'.$record->code.'-'.now()->format('YmdHis');
                    $remainingCreditBefore = CreditReconciliation::remainingCredit($record->white_company_id);

                    $creditData = $data;
                    $creditData['total_amount'] = $record->total_amount;

                    $documentPath = self::generateCreditNote($record, $creditData, $remainingCreditBefore, $noteNumber);

                    $paidMembership = $record->paid_memberships()->create([
                        'affiliation_id' => $record->id,
                        'agent_id' => $record->agent_id,
                        'code_agency' => $record->code_agency,
                        'plan_id' => $record->plan_id,
                        'coverage_id' => $record->coverage_id,
                        'total_amount' => $record->total_amount,
                        'pay_amount_usd' => $record->total_amount,
                        'pay_amount_ves' => 0.00,
                        'document_usd' => 'N/A',
                        'document_ves' => $documentPath,
                        'payment_method' => $data['payment_method'],
                        'payment_method_usd' => 'N/A',
                        'payment_method_ves' => 'N/A',
                        'payment_frequency' => $record['payment_frequency'],
                        'payment_date' => now()->format('d-m-Y'),
                        'prox_payment_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'observations_payment' => $data['observations_payment'] == null ? 'N/A' : $data['observations_payment'],
                        'renewal_date' => Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'))->addYear()->format('d-m-Y'),
                        'created_by' => Auth::user()->name,
                        'type_roll' => $type_roll,
                        'status' => 'APROBADO',
                        'invoice_number' => $noteNumber,
                    ]);

                    self::recordCreditMovement($record, $paidMembership, (float) $record->total_amount, $noteNumber);
                }

                /**
                 * Igual que en uploadPayment(): se propaga el white_company_id de cada
                 * afiliación a su comprobante recién creado y se publican los comprobantes
                 * como URL absoluta para Integracorp, sin tocar los 12 create() de arriba.
                 */
                $paidMembership = $record->paid_memberships()->latest()->first();

                if ($paidMembership) {
                    $paidMembership->update([
                        'white_company_id' => $record->white_company_id,
                        'document_usd' => self::publicDocumentUrl($paidMembership->document_usd),
                        'document_ves' => self::publicDocumentUrl($paidMembership->document_ves),
                    ]);
                }

                self::notifyPaymentProofUploaded($record, $paidMembership);

                /**
                 * A diferencia del resto de los métodos de pago (que en este flujo
                 * masivo quedan pendientes de aprobación manual), un pago a crédito
                 * se aprueba y activa de inmediato, igual que en uploadPayment().
                 */
                if ($data['payment_method'] == 'CREDITO') {
                    self::approveAndActivate($record, $data, $settlement);
                }
            }

            return true;

            // code...
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            Notification::make()
                ->title('EXCEPTION')
                ->body($th->getMessage())
                ->danger()
                ->send();
            // throw $th;
        }
    }
}
