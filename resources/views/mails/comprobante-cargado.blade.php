<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    :subject="$isCredito ? 'Nota de crédito generada' : 'Comprobante de pago cargado'"
    :footer-text="'Recibiste este correo porque está configurado como contacto de notificaciones de pago para la afiliación '.$affiliation->code.' en VivePlus.'"
>
    <x-slot:kicker>{{ $isCredito ? 'Pago a crédito' : 'Comprobante de pago' }}</x-slot:kicker>
    <x-slot:title>
        @if ($isCredito)
            Se generó una nota de crédito
        @else
            Se cargó un comprobante de pago
        @endif
    </x-slot:title>

    <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
        @if ($isCredito)
            Un analista de VivePlus aprobó un pago a crédito para la afiliación <strong style="color:#111827;">{{ $affiliation->code }}</strong>. Adjunta encontrarás la nota de crédito generada para este movimiento.
        @else
            Un analista de VivePlus cargó un comprobante de pago para la afiliación <strong style="color:#111827;">{{ $affiliation->code }}</strong>. Lo encontrarás adjunto a este correo.
        @endif
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#6b7280; width:40%;">Titular</td>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#111827; text-align:right;">{{ $affiliation->full_name_ti ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#6b7280;">Método de pago</td>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#111827; text-align:right;">{{ $paidMembership->payment_method }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#6b7280;">Monto</td>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#111827; text-align:right;">{{ number_format((float) $paidMembership->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#6b7280;">Frecuencia</td>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#111827; text-align:right;">{{ $paidMembership->payment_frequency }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#6b7280;">Fecha</td>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#111827; text-align:right;">{{ $paidMembership->payment_date }}</td>
        </tr>
        @if ($paidMembership->invoice_number)
            <tr>
                <td style="padding:10px 0; font-size:14px; color:#6b7280;">{{ $isCredito ? 'N.º de nota de crédito' : 'N.º de factura' }}</td>
                <td style="padding:10px 0; font-size:14px; color:#111827; text-align:right;">{{ $paidMembership->invoice_number }}</td>
            </tr>
        @endif
    </table>

    <p style="margin:24px 0 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
        Este es un aviso automático — no es necesario responder a este correo.
    </p>
</x-mail.layout>
