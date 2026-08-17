<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="Kit de Bienvenida"
    :footer-text="'Recibiste este correo porque tu afiliación '.$affiliation->code.' está activa en VivePlus.'"
>
    <x-slot:kicker>Afiliación aprobada</x-slot:kicker>
    <x-slot:title>
        @if ($firstName)
            ¡Bienvenido/a a VivePlus, {{ $firstName }}!
        @else
            ¡Ya eres oficialmente parte de VivePlus!
        @endif
    </x-slot:title>

    <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
        Te confirmamos que tu afiliación <strong style="color:#111827;">{{ $affiliation->code }}</strong> fue revisada y aprobada exitosamente. A partir de ahora ya cuentas con la cobertura de tu plan.
    </p>

    <p style="margin:24px 0 4px 0; font-size:14px; font-weight:700; color:#111827;">
        En este correo encontrarás
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px;">
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #f3f4f6; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">Certificado de afiliación</strong><br>
                tu comprobante oficial de que estás afiliado/a a VivePlus.
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #f3f4f6; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">Carnet de cada persona afiliada</strong><br>
                preséntalo cuando necesites usar tu plan.
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">Condicionado de tu plan</strong><br>
                el detalle de las coberturas y condiciones que aplican.
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
        Si tienes alguna pregunta sobre tu afiliación o tus documentos, escríbenos y con gusto te ayudamos.
    </p>
</x-mail.layout>
