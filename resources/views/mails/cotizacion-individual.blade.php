<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="Cotización Individual"
    footer-text="Has recibido este correo de acuerdo a lo solicitado por usted(es)."
>
    <x-slot:kicker>Tu cotización está lista</x-slot:kicker>
    <x-slot:title>¡Preparamos tu protección individual!</x-slot:title>

    <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
        Sabemos que tu tranquilidad y la de los tuyos es lo más importante. Por eso preparamos con especial detalle la cotización individual que nos solicitaste.
    </p>

    <p style="margin:20px 0 4px 0; font-size:14px; font-weight:700; color:#111827;">
        ¿Qué sigue ahora?
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px;">
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">1. Revisa el archivo adjunto</strong> con el detalle de tu cotización.
            </td>
        </tr>
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">2. Elige la opción</strong> que mejor se adapte a tu estilo de vida.
            </td>
        </tr>
        <tr>
            <td style="padding:10px 0; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">3. Comienza a disfrutar</strong> de todos nuestros beneficios.
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
        ¿Tienes dudas? No te preocupes, puedes contactarnos directamente a través de nuestras líneas de atención. ¡Bienvenido/a a la familia VivePlus!
    </p>
</x-mail.layout>
