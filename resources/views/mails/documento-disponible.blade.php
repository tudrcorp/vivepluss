<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCUMENTO DISPONIBLE</title>
</head>

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">

    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 680px; margin: 0 auto;">
        <tr>
            <td style="padding: 5px; background-color: #ffffff; border: 1px solid #e7e7e7; border-radius: 8px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding: 20px 20px 10px 20px; color: #333333;">
                            <span style="font-weight: bold; font-size: 18px;">{{ $documentTypeLabel }} disponible</span>
                            <p style="margin: 10px 0 0 0; color: #555555; font-size: 14px; line-height: 1.6;">
                                Integracorp entregó un documento nuevo para <strong>{{ $companyName }}</strong>.
                                Ya puedes descargarlo desde el panel de ViVEplus.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 10px 20px 20px 20px;">
                            <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px; color: #333333; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 40%; background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Afiliación</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $document->affiliation_code }}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Tipo de documento</td>
                                    <td style="border: 1px solid #e7e7e7;">{{ $documentTypeLabel }}</td>
                                </tr>
                                @if ($affiliateName)
                                    <tr>
                                        <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Afiliado</td>
                                        <td style="border: 1px solid #e7e7e7;">{{ $affiliateName }} ({{ $document->affiliate_identification }})</td>
                                    </tr>
                                @endif
                                @if ($document->generated_at)
                                    <tr>
                                        <td style="background-color: #f8f8f8; font-weight: bold; border: 1px solid #e7e7e7;">Generado por Integracorp</td>
                                        <td style="border: 1px solid #e7e7e7;">{{ $document->generated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 15px 20px; font-size: 10px; color: #999999; line-height: 14px; border-top: 1px solid #e7e7e7;">
                            Este es un correo automático generado por el sistema ViVEplus al recibir un documento nuevo de Integracorp.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
