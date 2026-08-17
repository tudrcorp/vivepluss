<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="{{ $documentTypeLabel }} disponible"
    footer-text="Este es un correo automático generado por el sistema ViVEplus al recibir un documento nuevo de Integracorp."
>
    <x-slot:kicker>Documento recibido</x-slot:kicker>
    <x-slot:title>{{ $documentTypeLabel }} disponible</x-slot:title>

    <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6; color:#4b5563;">
        Integracorp entregó un documento nuevo para <strong style="color:#111827;">{{ $companyName }}</strong>. Ya puedes descargarlo desde el panel de ViVEplus.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#374151; border-collapse:collapse;">
        <tr>
            <td style="width:40%; padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Afiliación</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $document->affiliation_code }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Tipo de documento</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $documentTypeLabel }}</td>
        </tr>
        @if ($affiliateName)
            <tr>
                <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Afiliado</td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $affiliateName }} ({{ $document->affiliate_identification }})</td>
            </tr>
        @endif
        @if ($document->generated_at)
            <tr>
                <td style="padding:10px 12px; background-color:#f9fafb; font-weight:600; border:1px solid #e5e7eb;">Generado por Integracorp</td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $document->generated_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endif
    </table>
</x-mail.layout>
