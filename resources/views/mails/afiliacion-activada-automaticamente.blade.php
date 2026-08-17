<x-mail.layout
    :logo-src="$logoPath && file_exists($logoPath) ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="Activación automática de afiliación"
    footer-text="Este es un correo automático generado por el sistema ViVEplus al aprobarse el primer comprobante de pago de una afiliación."
    width="680px"
>
    <x-slot:kicker>Activación automática</x-slot:kicker>
    <x-slot:title>Afiliación {{ $affiliation->code }} activada</x-slot:title>

    <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6; color:#4b5563;">
        La empresa <strong style="color:#111827;">{{ $companyName }}</strong> (white_company_id: {{ $affiliation->white_company_id }})
        cargó un comprobante de pago que activó de forma directa y automática la siguiente afiliación, sin requerir aprobación manual.
    </p>

    <div style="font-weight:700; font-size:14px; color:#111827; margin-bottom:8px;">Detalle de la afiliación</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#374151; border-collapse:collapse; margin-bottom:20px;">
        <tr>
            <td style="width:40%; padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Código</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->code }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Titular</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->full_name_ti }} ({{ $affiliation->nro_identificacion_ti }})</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Agencia</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->agency->name_corporative ?? $affiliation->code_agency }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Plan</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->plan->description ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Cobertura</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->coverage->price ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Frecuencia de pago</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->payment_frequency }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Monto total</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->total_amount }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Fecha de activación</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->activated_at }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Vigencia</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliation->effective_date }}</td>
        </tr>
    </table>

    @if ($paidMembership)
        <div style="font-weight:700; font-size:14px; color:#111827; margin-bottom:8px;">Detalle del pago</div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#374151; border-collapse:collapse;">
            @if (filled($paidMembership->payment_method))
                <tr>
                    <td style="width:40%; padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Método de pago</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->payment_method }}</td>
                </tr>
            @endif
            @if (filled($paidMembership->date_payment_voucher))
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Fecha del comprobante</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->date_payment_voucher }}</td>
                </tr>
            @endif
            @if (filled($paidMembership->total_amount))
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Monto pagado</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->total_amount }}</td>
                </tr>
            @endif
            @if ((float) $paidMembership->pay_amount_usd > 0)
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Monto en USD</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->pay_amount_usd }}</td>
                </tr>
            @endif
            @if ((float) $paidMembership->pay_amount_ves > 0)
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Monto en VES</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->pay_amount_ves }}</td>
                </tr>
            @endif
            @if ((float) $paidMembership->tasa_bcv > 0)
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Tasa BCV</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->tasa_bcv }}</td>
                </tr>
            @endif
            @if (filled($paidMembership->bank_usd) && $paidMembership->bank_usd !== 'N/A')
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Banco (USD)</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->bank_usd }}</td>
                </tr>
            @endif
            @if (filled($paidMembership->bank_ves) && $paidMembership->bank_ves !== 'N/A')
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Banco (VES)</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->bank_ves }}</td>
                </tr>
            @endif
            @if (filled($referenceZelle))
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Referencia Zelle</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $referenceZelle }}</td>
                </tr>
            @endif
            @if (filled($paidMembership->reference_payment_ves) && $paidMembership->reference_payment_ves !== 'N/A')
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Referencia (VES)</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->reference_payment_ves }}</td>
                </tr>
            @endif
            @if (filled($paidMembership->name_ti_usd) && $paidMembership->name_ti_usd !== 'N/A')
                <tr>
                    <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Titular del comprobante</td>
                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $paidMembership->name_ti_usd }}</td>
                </tr>
            @endif
        </table>

        @if (filled($paidMembership->observations_payment) && $paidMembership->observations_payment !== 'N/A')
            <div style="margin-top:12px; padding:10px 12px; background-color:#fff8e1; border:1px solid #f0e2ab; border-radius:6px; font-size:13px; color:#6b5c1e;">
                <strong>Observación del analista:</strong> {{ $paidMembership->observations_payment }}
            </div>
        @endif

        <div style="margin-top:20px;"></div>
    @endif

    <div style="font-weight:700; font-size:14px; color:#111827; margin-bottom:8px;">Afiliados asociados</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#374151; border-collapse:collapse;">
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Nombre</td>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">CI</td>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Parentesco</td>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Fecha de nacimiento</td>
        </tr>
        @forelse ($affiliation->affiliates as $affiliate)
            <tr>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliate->full_name }}</td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliate->nro_identificacion }}</td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliate->relationship }}</td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliate->birth_date }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="padding:10px 12px; border:1px solid #e5e7eb; color:#9ca3af;">No hay afiliados adicionales registrados.</td>
            </tr>
        @endforelse
    </table>
</x-mail.layout>
