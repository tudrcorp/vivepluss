<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="Activación en VivePlus"
    footer-text="Has recibido este correo porque se activó una cuenta a tu nombre en la fuerza de venta de VivePlus."
>
    <x-slot:kicker>Activación exitosa</x-slot:kicker>
    <x-slot:title>¡Bienvenido/a a la fuerza de venta de VivePlus, {{ $name }}!</x-slot:title>

    <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
        Te confirmamos que tu activación como <strong style="color:#111827;">{{ $roleLabel }}</strong> dentro de la fuerza de venta de VivePlus se ha realizado con éxito. Ya puedes ingresar al panel con los siguientes datos:
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px; background-color:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 10px 0; font-size:13px; color:#6b7280;">
                    Usuario<br>
                    <strong style="font-size:15px; color:#111827;">{{ $email }}</strong>
                </p>
                <p style="margin:0; font-size:13px; color:#6b7280;">
                    Contraseña<br>
                    <strong style="font-size:15px; color:#111827;">12345678</strong>
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:20px 0 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
        Por tu seguridad, te recomendamos cambiar esta contraseña cuando gustes haciendo clic en
        <a href="{{ $resetPasswordUrl }}" style="color:{{ $primaryColor }}; font-weight:600; text-decoration:none;">¿Ha olvidado su contraseña?</a>
        desde la pantalla de inicio de sesión.
    </p>

    <p style="margin:16px 0 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
        Si tienes alguna pregunta, no dudes en contactarnos.
    </p>
</x-mail.layout>
