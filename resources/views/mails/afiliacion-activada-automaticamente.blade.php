<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACTIVACIÓN AUTOMÁTICA DE AFILIACIÓN</title>
</head>

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">

    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 680px; margin: 0 auto;">
        <tr>
            <td style="padding: 5px; background-color: #ffffff; border: 1px solid #e7e7e7; border-radius: 8px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    @if ($logoPath && file_exists($logoPath))
                        <tr>
                            <td align="center" style="padding: 20px 20px 0 20px;">
                                <img src="{{ $message->embed($logoPath) }}" alt="{{ $companyName }}" style="max-height: 60px; max-width: 220px; height: auto; width: auto;">
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding: 20px 20px 10px 20px; color: #333333;">
                            <span style="font-weight: bold; font-size: 18px;">Activación automática de afiliación</span>
                            <p style="margin: 10px 0 0 0; color: #555555; font-size: 14px; line-height: 1.6;">
                                La empresa <strong>{{ $companyName }}</strong> (white_company_id: {{ $affiliation->white_company_id }})
                                cargó un comprobante de pago que activó de forma directa y automática la siguiente afiliación,
                                sin requerir aprobación manual.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 10px 20px;">
                            <div style="font-weight: bold; font-size: 14px; color: #333333; margin-bottom: 8px;">Detalle de la afiliación</div>
                            <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px; color: #333333; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 40%; background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Código</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->code }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Titular</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->full_name_ti }} ({{ $affiliation->nro_identificacion_ti }})</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Agencia</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->agency->name_corporative ?? $affiliation->code_agency }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Plan</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->plan->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Cobertura</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->coverage->price ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Frecuencia de pago</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->payment_frequency }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Monto total</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->total_amount }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Fecha de activación</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->activated_at }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Vigencia</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $affiliation->effective_date }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if ($paidMembership)
                        <tr>
                            <td style="padding: 10px 20px;">
                                <div style="font-weight: bold; font-size: 14px; color: #333333; margin-bottom: 8px;">Detalle del pago</div>
                                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px; color: #333333; border-collapse: collapse;">
                                    @if (filled($paidMembership->payment_method))
                                        <tr>
                                            <td style="width: 40%; background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Método de pago</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->payment_method }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($paidMembership->date_payment_voucher))
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Fecha del comprobante</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->date_payment_voucher }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($paidMembership->total_amount))
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Monto pagado</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->total_amount }}</td>
                                        </tr>
                                    @endif
                                    @if ((float) $paidMembership->pay_amount_usd > 0)
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Monto en USD</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->pay_amount_usd }}</td>
                                        </tr>
                                    @endif
                                    @if ((float) $paidMembership->pay_amount_ves > 0)
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Monto en VES</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->pay_amount_ves }}</td>
                                        </tr>
                                    @endif
                                    @if ((float) $paidMembership->tasa_bcv > 0)
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Tasa BCV</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->tasa_bcv }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($paidMembership->bank_usd) && $paidMembership->bank_usd !== 'N/A')
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Banco (USD)</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->bank_usd }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($paidMembership->bank_ves) && $paidMembership->bank_ves !== 'N/A')
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Banco (VES)</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->bank_ves }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($referenceZelle))
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Referencia Zelle</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $referenceZelle }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($paidMembership->reference_payment_ves) && $paidMembership->reference_payment_ves !== 'N/A')
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Referencia (VES)</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->reference_payment_ves }}</td>
                                        </tr>
                                    @endif
                                    @if (filled($paidMembership->name_ti_usd) && $paidMembership->name_ti_usd !== 'N/A')
                                        <tr>
                                            <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Titular del comprobante</td>
                                            <td style="border: 1px solid #e7e7e7;">{{ $paidMembership->name_ti_usd }}</td>
                                        </tr>
                                    @endif
                                </table>

                                @if (filled($paidMembership->observations_payment) && $paidMembership->observations_payment !== 'N/A')
                                    <div style="margin-top: 10px; padding: 10px 12px; background-color: #fff8e1; border: 1px solid #f0e2ab; border-radius: 4px; font-size: 13px; color: #6b5c1e;">
                                        <strong>Observación del analista:</strong> {{ $paidMembership->observations_payment }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding: 10px 20px 20px 20px;">
                            <div style="font-weight: bold; font-size: 14px; color: #333333; margin-bottom: 8px;">Afiliados asociados</div>
                            <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px; color: #333333; border-collapse: collapse;">
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Nombre</td>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">CI</td>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Parentesco</td>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Fecha de nacimiento</td>
                                </tr>
                                @forelse ($affiliation->affiliates as $affiliate)
                                    <tr>
                                        <td style="border: 1px solid #e7e7e7;">{{ $affiliate->full_name }}</td>
                                        <td style="border: 1px solid #e7e7e7;">{{ $affiliate->nro_identificacion }}</td>
                                        <td style="border: 1px solid #e7e7e7;">{{ $affiliate->relationship }}</td>
                                        <td style="border: 1px solid #e7e7e7;">{{ $affiliate->birth_date }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="border: 1px solid #e7e7e7; color: #777777;">No hay afiliados adicionales registrados.</td>
                                    </tr>
                                @endforelse
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 15px 20px; font-size: 10px; color: #999999; line-height: 14px; border-top: 1px solid #e7e7e7;">
                            Este es un correo automático generado por el sistema ViVEplus al aprobarse el primer comprobante de pago de una afiliación.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
