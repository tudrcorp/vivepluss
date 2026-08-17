<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="Cotización Corporativa"
    footer-text="Ha recibido este correo de acuerdo a lo solicitado por usted(es)."
>
    <x-slot:kicker>Tu cotización está lista</x-slot:kicker>
    <x-slot:title>Soluciones de salud para su organización</x-slot:title>

    <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
        Es un gusto saludarles. En VivePlus entendemos que el capital humano es el activo más valioso de cualquier empresa. Por ello preparamos la cotización corporativa solicitada, diseñada para brindar el respaldo y la protección que su equipo de trabajo merece.
    </p>

    <p style="margin:20px 0 4px 0; font-size:14px; font-weight:700; color:#111827;">
        Pasos para el éxito
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px;">
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">1. Analice la propuesta adjunta</strong> y sus beneficios exclusivos.
            </td>
        </tr>
        <tr>
            <td style="padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">2. Seleccione el plan</strong> que impulsará el bienestar de su organización.
            </td>
        </tr>
        <tr>
            <td style="padding:10px 0; font-size:14px; line-height:1.55; color:#4b5563;">
                <strong style="color:#111827;">3. Contáctenos</strong> para activar la cobertura de inmediato.
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
        Estamos listos para ser sus aliados estratégicos. ¿Agendamos una breve llamada para concretar detalles? ¡Bienvenido/a a la familia VivePlus!
    </p>
</x-mail.layout>
