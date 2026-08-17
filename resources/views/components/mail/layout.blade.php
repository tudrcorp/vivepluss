@props([
    'logoSrc' => null,
    'primaryColor' => '#A13DDB',
    'subject' => 'VivePlus',
    'footerText' => null,
    'width' => '520px',
])
{{--
    Cascarón visual único para todos los correos de la app: logo, tarjeta
    blanca con borde redondeado, kicker/título opcionales y footer. Cada
    vista de mails/ solo aporta el contenido del slot por defecto (y, si
    necesita logo, resuelve $message->embed($logoPath) ANTES de invocar
    este componente y lo pasa como :logo-src — un componente Blade no
    hereda automáticamente el $message del Mailable padre).
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:{{ $width }}; background-color:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">

                    {{-- Logo --}}
                    <tr>
                        <td align="center" style="padding:36px 40px 24px 40px;">
                            @if ($logoSrc)
                                <img src="{{ $logoSrc }}" alt="VivePlus" style="max-height:44px; width:auto; display:block;">
                            @else
                                <span style="font-size:20px; font-weight:700; letter-spacing:0.3px; color:{{ $primaryColor }};">VivePlus</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:none; border-top:1px solid #e5e7eb; margin:0;">
                        </td>
                    </tr>

                    {{-- Kicker + título --}}
                    @isset($kicker)
                        <tr>
                            <td style="padding:32px 40px 0 40px;">
                                <p style="margin:0 0 6px 0; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:{{ $primaryColor }};">
                                    {{ $kicker }}
                                </p>
                            </td>
                        </tr>
                    @endisset
                    @isset($title)
                        <tr>
                            <td style="padding:{{ isset($kicker) ? '0' : '32px' }} 40px 0 40px;">
                                <h1 style="margin:0; font-size:21px; line-height:1.35; color:#111827; font-weight:700;">
                                    {{ $title }}
                                </h1>
                            </td>
                        </tr>
                    @endisset

                    {{-- Contenido --}}
                    <tr>
                        <td style="padding:14px 40px 32px 40px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    @if ($footerText)
                        <tr>
                            <td style="padding:18px 40px; background-color:#fafafa; border-top:1px solid #e5e7eb;">
                                <p style="margin:0; font-size:12px; line-height:1.5; color:#9ca3af;">
                                    {{ $footerText }}
                                </p>
                            </td>
                        </tr>
                    @endif

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
