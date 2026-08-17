<x-mail.layout
    :logo-src="$logoPath ? $message->embed($logoPath) : null"
    :primary-color="$primaryColor"
    subject="Certificado de Afiliación"
    footer-text="Has recibido este correo porque estás afiliado/a a VivePlus."
>
    <x-slot:kicker>Afiliación aprobada</x-slot:kicker>
    <x-slot:title>¡Ya eres oficialmente parte de VivePlus!</x-slot:title>

    <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">
        Adjuntamos tu certificado de afiliación para que puedas revisarlo y guardarlo de inmediato. En él encontrarás toda la información referente a tu vinculación con VivePlus.
    </p>

    <p style="margin:16px 0 0 0; font-size:15px; line-height:1.6; color:#4b5563;">
        ¡Bienvenido/a a la comunidad! Si tienes alguna pregunta, no dudes en contactarnos.
    </p>
</x-mail.layout>
