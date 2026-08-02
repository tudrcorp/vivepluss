@php
use App\Models\Configuration;
// Configuración defensiva para prevenir fallos si el registro no existe
$defaultConfig = [
'web_headTitle' => 'Integracorp | VivePluss',
'web_headDescription' => 'Vivepluss: Soluciones de seguros y asistencia.',
'web_headKeywords' => 'Integracorp, Seguros, Asistencia, Vivepluss',
'web_headOpTitle' => 'VivePluss - Siempre contigo',
'web_headOpDescription' => 'Tu plataforma integral de bienestar.',
'web_headXTitle' => 'VivePluss',
'web_headXDescription' => 'Conoce nuestros planes.',
'web_sectionOne_title' => 'Tu tranquilidad es nuestra prioridad.',
'web_sectionOne_title_ln_2' => 'Descubre el plan perfecto para ti.',
'web_headerLogo' => 'images/ViveplussBlanco.png', // Placeholder
'web_footerCopy' => 'Integracorp © 2024. Todos los derechos reservados.',
'web_footerContactEmail' => 'info@vivepluss.com',
'web_footerContactPhone' => '+58 422-5577557',
'web_icons_redSocial' => null,
'web_url_facebook' => null,
'web_url_instagram' => null,
'web_url_twitter' => null,
'web_url_whatsapp' => null,
];

// Obtener el registro, o usar un objeto anónimo basado en $defaultConfig si es null
$setting = Configuration::first() ?? (object) $defaultConfig;

$contactEmail = $setting->web_footerContactEmail ?: 'info@vivepluss.com';
$contactPhone = $setting->web_footerContactPhone ?: '+58 422-5577557';
$whatsappNumber = preg_replace('/\D+/', '', $contactPhone);
$whatsappUrl = filled($setting->web_url_whatsapp ?? null)
    ? $setting->web_url_whatsapp
    : ('https://wa.me/' . $whatsappNumber);

$socialLinks = [
    'fab fa-facebook-f' => filled($setting->web_url_facebook ?? null) ? $setting->web_url_facebook : '#',
    'fab fa-instagram' => filled($setting->web_url_instagram ?? null) ? $setting->web_url_instagram : '#',
    'fab fa-twitter' => filled($setting->web_url_twitter ?? null) ? $setting->web_url_twitter : '#',
    'fab fa-whatsapp' => $whatsappUrl,
];

$socialLabels = [
    'fab fa-facebook-f' => 'Facebook',
    'fab fa-instagram' => 'Instagram',
    'fab fa-twitter' => 'X (Twitter)',
    'fab fa-whatsapp' => 'WhatsApp',
];

$instagramUrl = filled($setting->web_url_instagram ?? null)
    ? $setting->web_url_instagram
    : 'https://www.instagram.com/';

$plansTitleRaw = (string) ($setting->web_plansTitle ?? 'Elige el plan perfecto para ti');
$plansTitleLower = mb_strtolower($plansTitleRaw);
$plansTitleCased = mb_strtoupper(mb_substr($plansTitleLower, 0, 1)) . mb_substr($plansTitleLower, 1);
$plansTitleFormatted = preg_replace(
    '/perfecto/iu',
    '<span class="planes-keyword">PERFECTO</span>',
    e($plansTitleCased)
);

// $setting = Configuration::first();
@endphp
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>{{ $setting->web_headTitle }}</title>


    <!--SEO-->
    <meta name="description" content="{{ $setting->web_headDescription }}">
    <meta name="keywords" content="{{ $setting->web_headKeywords }}">
    <meta name="author" content="Integracorp">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://www.vivepluss.com/">

    <meta property="og:title" content="{{ $setting->web_headOpTitle }}">
    <meta property="og:description" content="{{ $setting->web_headOpDescription }}">


    <!-- ATENCIÓN: Se usó un placeholder para la imagen, reemplace con la ruta real de Laravel si es necesario -->
    <meta property="og:image" content="{{ asset('images/ViveplussBlanco.png') }}">

    <meta property="og:url" content="https://www.vivepluss.com/">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@Integracorp">
    <meta name="twitter:creator" content="@integracorp">
    <meta name="twitter:title" content="{{ $setting->web_headXTitle }}">
    <meta name="twitter:description" content="{{ $setting->web_headXDescription }}">
    <meta name="twitter:image" content="{{ asset('images/ViveplussBlanco.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ViveplussBlanco.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/ViveplussBlanco.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/ViveplussBlanco.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/ViveplussBlanco.png') }}">

    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>


    <!-- Estilos Inline en el Head -->
    <style>
        :root {
            /* Colores Base */
            --primary: #A13DDB;
            /* Magenta/Púrpura */
            --secondary: #71BAFF;
            /* Azul Claro */
            --light-blue: #096FFF;
            /* Azul brillante */

            /* Fondos y Texto */
            --bg-light: #EFEFEF;
            --bg-lighter: #F6F6F7;
            --text-dark: #333;
            --text-light: #666;
            --transition: all 0.3s ease;

            /* Nuevo para Resaltar Plan */
            --highlight-bg: #f5f0fb;
            /* Fondo muy claro basado en Primary */
            --highlight-border: var(--primary);
            --highlight-shadow: rgba(161, 61, 219, 0.2);

            /* Custom dark background for the footer */
            --footer-dark-bg: #1A112A;

        }

        /* Estilos personalizados usando las variables CSS */
        .theme-primary-bg {
            background-color: var(--primary);
        }

        .theme-primary-text {
            color: var(--primary);
        }

        .theme-bg-light {
            background-color: var(--bg-light);
        }

        .theme-text-dark {
            color: var(--text-dark);
        }

        .theme-border-gray {
            border-color: #e5e7eb;
        }

        /* Un gris suave */

        .slider-container {
            overflow: hidden;
            transform: translate3d(0, 0, 0);
        }

        .comments-list {
            display: flex;
            /* La animación es la clave del diseño moderno y minimalista */
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .comment-card {
            flex: 0 0 100%;
            padding: 2.5rem;
        }

        /* Aplica la sombra de acento a los botones de navegación al hacer hover */
        .nav-btn:hover {
            box-shadow: 0 4px 15px var(--highlight-shadow);
        }

        /* Estilo para el botón principal */
        .theme-btn {
            background-color: var(--primary);
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .theme-btn:hover {
            background-color: var(--light-blue);
            /* Cambio de color al azul brillante */
            box-shadow: 0 8px 20px var(--highlight-shadow);
            transform: translateY(-2px);
        }



        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Quicksand', sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-light);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ESTILO PARA LA IMAGEN DEL LOGO */
        .logo {
            position: absolute;
            padding: 1rem;
            top: 0rem;
            right: 2rem;
            z-index: 20;
            transition: var(--transition);
            height: 130px;
            /* Ajusta la altura del logo */
            width: auto;
            /* Mantiene la proporción */
        }


        .logo-placeholder {
            height: 50px;
            width: 50px;
            /* Usar un placeholder con el color primario y texto para simular el logo */
            /* background-color: var(--primary); */
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            border-radius: 8px;
            content: 'TD';
            overflow: hidden;
        }

        /* Omitiendo estilos de video y menu que no fueron cambiados significativamente para mantener el foco en los planes */

        /* === SECCIÓN PLANES MEJORADA === */
        .section-planes {
            padding: 4rem 1.5rem;
            background-color: white;
        }

        .section-planes h2 {
            font-size: 2.2rem;
            font-weight: 300;
            color: var(--text-dark);
            /* Color más oscuro para mejor contraste */
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .section-planes h2 .highlight {
            color: var(--primary);
            font-weight: 700;
            font-size: 2.2rem;
        }

        .planes-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            /* Aumento de gap para mejor separación */
            max-width: 1200px;
            margin: 0 auto;
        }

        .plan-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            /* Más redondeado */
            padding: 2rem 1.5rem;
            /* Más relleno */
            width: 100%;
            max-width: 380px;
            /* Ancho sutilmente mayor */
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            /* Sombra más pronunciada */
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .plan-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 15px 30px var(--highlight-shadow);
            border-color: var(--primary);
        }

        /* Estilo para el plan IDEAL (Destacado) */
        .plan-card.is-recommended {
            background-color: var(--highlight-bg);
            /* Fondo sutil */
            border: 3px solid var(--primary);
            /* Borde primario grueso */
            box-shadow: 0 10px 25px var(--highlight-shadow);
            transform: translateY(-5px);
            /* Se mantiene ligeramente levantado */
        }

        .plan-card.is-recommended:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 20px 40px var(--highlight-shadow);
        }

        .plan-card h3 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .plan-card .price-tag {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .plan-card .price-tag span {
            font-size: 1.5rem;
            font-weight: 500;
            vertical-align: top;
            margin-right: 2px;
            color: var(--text-light);
        }

        .plan-card .frequency {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .plan-card ul {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            /* Más espacio vertical */
            text-align: left;
            width: 100%;
            flex-grow: 1;
            /* Permite que la lista empuje el botón hacia abajo */
        }

        .plan-card li {
            color: var(--text-light);
            font-size: 0.95rem;
            margin: 0.7rem 0;
            /* Más espacio entre ítems */
            position: relative;
            padding-left: 1.8rem;
            display: flex;
            align-items: center;
            font-weight: 400;
        }

        /* Estilo mejorado para el ícono de las características */
        .plan-card li .feature-icon {
            color: var(--secondary);
            /* Color secundario para el ícono */
            position: absolute;
            left: 0;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Estilo para destacar las características ÚNICAS */
        .plan-card li.is-highlighted {
            font-weight: 600;
            color: var(--primary);
            background-color: rgba(161, 61, 219, 0.05);
            padding: 0.4rem 0.5rem;
            margin: 0.7rem -0.5rem;
            border-radius: 4px;
        }

        .plan-card li.is-highlighted .feature-icon {
            color: var(--primary);
        }

        .plan-card a.plan-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.8rem;
            /* Botón más grande */
            background-color: var(--secondary);
            /* Por defecto, el botón es azul claro */
            color: white;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Botón del plan IDEAL */
        .plan-card.is-recommended a.plan-btn {
            background-color: var(--primary);
            /* El más importante usa el color primario */
            box-shadow: 0 6px 15px var(--highlight-shadow);
        }

        .plan-card a.plan-btn:hover {
            opacity: 0.9;
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        /* Ajustes de responsive para la sección de planes */
        @media (max-width: 1024px) {
            .planes-container {
                gap: 1.5rem;
            }

            .plan-card {
                max-width: 340px;
            }
        }

        @media (max-width: 768px) {
            .planes-container {
                gap: 2rem;
                flex-direction: column;
                align-items: center;
            }

            .plan-card {
                max-width: 90%;
            }

            .section-planes h2 {
                font-size: 1.8rem;
            }
        }

        /* Omitiendo otros estilos que no fueron modificados */

        /* === ESTILOS ORIGINALES NO RELACIONADOS CON PLANES === */

        /* === SECCIÓN VIDEO FULLSCREEN === */
        .fullscreen-video {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .fullscreen-video video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                radial-gradient(ellipse 70% 55% at 50% 45%, rgba(0, 0, 0, 0.28) 0%, rgba(0, 0, 0, 0.55) 55%, rgba(0, 0, 0, 0.72) 100%),
                linear-gradient(180deg, rgba(5, 47, 96, 0.45) 0%, rgba(0, 0, 0, 0.35) 40%, rgba(0, 0, 0, 0.65) 100%);
            box-shadow: inset 0 0 120px rgba(0, 0, 0, 0.45);
        }

        /* .logo {
            position: absolute;
            padding: 1rem;
            top: 1rem;
            right: 1rem;
            z-index: 20;
            transition: var(--transition);
        }

        .logo img {
            height: 200px;
            width: 200px;
        } */

        /* === MENÚ SUPERIOR (Escritorio) === */
        .menu-desktop {
            position: absolute;
            padding: 1rem;
            top: 1rem;
            left: 1rem;
            z-index: 20;
        }

        .menu-desktop ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
        }

        .menu-desktop a {
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 400;
            letter-spacing: 0.3px;
            transition: var(--transition);
        }

        .menu-desktop a:hover {
            color: var(--light-blue);
            /* transform: translateY(-2px); */
            transform: scale(1.1) translateY(-3px);
        }

        /* === MENÚ HAMBURGUESA (Móvil) === */
        .menu-mobile {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 20;
            display: none;
            /* Oculto por defecto */
            cursor: pointer;
            color: white;
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .menu-mobile:hover {
            color: var(--light-blue);
        }

        /* Panel desplegable (oculto por defecto) */
        .mobile-menu-panel {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 30;
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }

        .mobile-menu-panel.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .mobile-menu-panel ul {
            list-style: none;
            text-align: center;
            width: 80%;
            max-width: 300px;
        }

        .mobile-menu-panel a {
            display: block;
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            transition: var(--transition);
            letter-spacing: 0.5px;
        }

        .mobile-menu-panel a:hover {
            background-color: var(--primary);
            color: var(--light-blue);
        }

        .close-menu {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-menu:hover {
            color: var(--light-blue);
        }

        /* === REDES SOCIALES === */
        .social-icons {
            position: absolute;
            padding: 1rem;
            bottom: 1rem;
            left: 1rem;
            z-index: 20;
            display: flex;
            gap: 1rem;
        }

        .social-icons a {
            color: white;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .social-icons a:hover {
            color: var(--light-blue);
            transform: scale(1.2) translateY(-3px);
        }

        /* === NAVBAR HERO (mismo blanco / azul que redes) === */
        .hero-nav-links [data-flux-navbar-items] {
            color: white !important;
            transition: var(--transition);
        }

        .hero-nav-links [data-flux-navbar-items]:hover {
            color: var(--light-blue) !important;
            background-color: transparent !important;
        }

        /* === SECCIONES === */
        .section-nosotros {
            position: relative;
            padding: 6.5rem 1.5rem 5.5rem;
            background:
                radial-gradient(ellipse 80% 60% at 50% 0%, rgba(161, 61, 219, 0.07), transparent 55%),
                radial-gradient(ellipse 50% 40% at 80% 80%, rgba(113, 186, 255, 0.08), transparent 50%),
                var(--bg-lighter);
            text-align: center;
            overflow: hidden;
        }

        /* Puente visual hero → nosotros / Instagram → footer (ola animada sin corte) */
        .nosotros-bridge,
        .footer-bridge {
            position: absolute;
            top: -1px;
            left: 0;
            width: 100%;
            height: clamp(64px, 11vw, 110px);
            line-height: 0;
            pointer-events: none;
            z-index: 2;
            overflow: hidden;
        }

        .nosotros-bridge .wave-layer,
        .footer-bridge .wave-layer {
            position: absolute;
            left: 0;
            bottom: 0;
            display: flex;
            width: 300%;
            height: 100%;
            will-change: transform;
        }

        .nosotros-bridge .wave-layer svg,
        .footer-bridge .wave-layer svg {
            display: block;
            flex: 0 0 33.333333%;
            width: 33.333333%;
            height: 100%;
        }

        .nosotros-bridge .wave-layer--back,
        .footer-bridge .wave-layer--back {
            opacity: 0.85;
            animation: waveDrift 22s linear infinite;
        }

        .nosotros-bridge .wave-layer--front,
        .footer-bridge .wave-layer--front {
            opacity: 1;
            animation: waveDrift 15s linear infinite;
            animation-delay: -4s;
        }

        .footer-bridge {
            background-color: #1A112A;
        }

        .footer-bridge::after {
            display: none;
        }

        .footer-bridge .wave-layer {
            z-index: 1;
        }

        .site-footer {
            position: relative;
            overflow: hidden;
            padding-top: calc(clamp(64px, 11vw, 110px) + 2.5rem);
        }

        .site-footer > .container {
            position: relative;
            z-index: 1;
        }

        .site-footer .footer-main-grid,
        .site-footer .footer-copy {
            border: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        @keyframes waveDrift {
            from { transform: translate3d(0, 0, 0); }
            to { transform: translate3d(-33.333333%, 0, 0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .nosotros-bridge .wave-layer--back,
            .nosotros-bridge .wave-layer--front,
            .footer-bridge .wave-layer--back,
            .footer-bridge .wave-layer--front {
                animation: none;
            }
        }

        .section-nosotros::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, transparent 40%, rgba(255, 255, 255, 0.45) 50%, transparent 60%);
            background-size: 220% 100%;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.6s ease;
        }

        .section-nosotros.is-visible::before {
            opacity: 1;
            animation: nosotrosShimmer 1.8s ease-out forwards;
        }

        @keyframes nosotrosShimmer {
            from { background-position: 120% 0; }
            to { background-position: -40% 0; }
        }

        .section-nosotros .nosotros-content {
            position: relative;
            z-index: 1;
            max-width: 1040px;
            margin: 0 auto;
        }

        .section-nosotros h2 {
            font-size: clamp(2.35rem, 4.2vw, 3.15rem);
            font-weight: 300;
            color: var(--primary);
            margin-bottom: 0.75rem;
            opacity: 0;
            transform: translateY(28px) scale(0.97);
            filter: blur(8px);
            transition:
                opacity 1.8s cubic-bezier(0.22, 1, 0.36, 1),
                transform 1.8s cubic-bezier(0.22, 1, 0.36, 1),
                filter 1.8s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .section-nosotros .nosotros-accent {
            display: block;
            width: 0;
            height: 3px;
            margin: 0 auto 1.85rem;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--light-blue));
            box-shadow: 0 0 18px rgba(113, 186, 255, 0.45);
            transition: width 2s cubic-bezier(0.22, 1, 0.36, 1) 0.45s;
        }

        .section-nosotros h2 .highlight {
            font-size: clamp(2.5rem, 4.8vw, 3.5rem);
        }

        .section-nosotros p {
            max-width: 960px;
            margin: 0 auto;
            font-size: clamp(1.25rem, 2vw, 1.45rem);
            line-height: 1.75;
            color: var(--text-light);
            opacity: 0;
            transform: translateY(36px);
            filter: blur(6px);
            transition:
                opacity 2s cubic-bezier(0.22, 1, 0.36, 1) 0.55s,
                transform 2s cubic-bezier(0.22, 1, 0.36, 1) 0.55s,
                filter 2s cubic-bezier(0.22, 1, 0.36, 1) 0.55s,
                box-shadow 0.6s ease,
                border-color 0.6s ease;
        }

        .section-nosotros.is-visible h2 {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0);
        }

        .section-nosotros.is-visible .nosotros-accent {
            width: min(420px, 75%);
        }

        .section-nosotros.is-visible p {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .section-nosotros::before,
            .section-nosotros.is-visible::before {
                animation: none;
                opacity: 0;
            }

            .section-nosotros h2,
            .section-nosotros p,
            .section-nosotros .nosotros-accent {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
                width: min(420px, 75%);
            }
        }

        /* === Reveal Misión / secciones split === */
        .reveal-on-scroll .reveal-from-left,
        .reveal-on-scroll .reveal-from-right {
            opacity: 0;
            filter: blur(6px);
            transition:
                opacity 1.9s cubic-bezier(0.22, 1, 0.36, 1),
                transform 1.9s cubic-bezier(0.22, 1, 0.36, 1),
                filter 1.9s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .reveal-on-scroll .reveal-from-left {
            transform: translateX(-48px);
        }

        .reveal-on-scroll .reveal-from-right {
            transform: translateX(48px);
            transition-delay: 0.35s;
        }

        .reveal-on-scroll .reveal-from-left .reveal-title,
        .reveal-on-scroll .reveal-from-right .reveal-title {
            opacity: 0;
            transform: translateY(18px);
            transition:
                opacity 1.5s cubic-bezier(0.22, 1, 0.36, 1) 0.25s,
                transform 1.5s cubic-bezier(0.22, 1, 0.36, 1) 0.25s;
        }

        .reveal-on-scroll .reveal-from-left .reveal-text,
        .reveal-on-scroll .reveal-from-right .reveal-text {
            opacity: 0;
            transform: translateY(22px);
            transition:
                opacity 1.7s cubic-bezier(0.22, 1, 0.36, 1) 0.55s,
                transform 1.7s cubic-bezier(0.22, 1, 0.36, 1) 0.55s;
        }

        .reveal-on-scroll.is-visible .reveal-from-left,
        .reveal-on-scroll.is-visible .reveal-from-right {
            opacity: 1;
            transform: translateX(0);
            filter: blur(0);
        }

        .reveal-on-scroll.is-visible .reveal-from-left .reveal-title,
        .reveal-on-scroll.is-visible .reveal-from-left .reveal-text,
        .reveal-on-scroll.is-visible .reveal-from-right .reveal-title,
        .reveal-on-scroll.is-visible .reveal-from-right .reveal-text {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 1023px) {
            .reveal-on-scroll .reveal-from-left,
            .reveal-on-scroll .reveal-from-right {
                transform: translateY(36px);
            }

            .reveal-on-scroll.is-visible .reveal-from-left,
            .reveal-on-scroll.is-visible .reveal-from-right {
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal-on-scroll .reveal-from-left,
            .reveal-on-scroll .reveal-from-right,
            .reveal-on-scroll .reveal-from-left .reveal-title,
            .reveal-on-scroll .reveal-from-left .reveal-text,
            .reveal-on-scroll .reveal-from-right .reveal-title,
            .reveal-on-scroll .reveal-from-right .reveal-text {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
            }
        }

        /* === Fondo decorativo Misión / Visión (animado) === */
        .section-brand-atmosphere {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: linear-gradient(180deg, #ffffff 0%, #f7f9fc 48%, #f3f6fb 100%);
        }

        .section-brand-atmosphere--alt {
            background: linear-gradient(180deg, #f6f8fb 0%, #eef3f9 50%, #f8fafc 100%);
        }

        .section-brand-atmosphere .brand-bg-mesh {
            position: absolute;
            inset: -25%;
            background:
                radial-gradient(ellipse 45% 40% at 20% 40%, rgba(161, 61, 219, 0.14), transparent 60%),
                radial-gradient(ellipse 40% 45% at 80% 55%, rgba(9, 111, 255, 0.13), transparent 58%),
                radial-gradient(ellipse 35% 30% at 50% 90%, rgba(113, 186, 255, 0.14), transparent 55%);
            animation: brandMeshDrift 18s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: 0;
        }

        .section-brand-atmosphere::before {
            content: '';
            position: absolute;
            inset: -10%;
            background-image:
                radial-gradient(rgba(161, 61, 219, 0.1) 1.2px, transparent 1.2px);
            background-size: 28px 28px;
            mask-image: radial-gradient(ellipse 70% 60% at 50% 45%, #000 20%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 45%, #000 20%, transparent 75%);
            opacity: 0.5;
            pointer-events: none;
            z-index: 0;
            animation: brandDotsDrift 28s linear infinite;
        }

        .section-brand-atmosphere::after {
            content: '';
            position: absolute;
            width: min(420px, 55vw);
            height: min(420px, 55vw);
            right: -8%;
            top: 12%;
            border-radius: 50%;
            border: 1px solid rgba(113, 186, 255, 0.22);
            box-shadow:
                0 0 0 28px rgba(161, 61, 219, 0.03),
                0 0 0 56px rgba(9, 111, 255, 0.025);
            pointer-events: none;
            z-index: 0;
            animation: brandRingPulse 10s ease-in-out infinite;
        }

        .section-brand-atmosphere--mirror::after {
            right: auto;
            left: -8%;
            top: auto;
            bottom: 10%;
            animation-delay: -3s;
        }

        .section-brand-atmosphere .brand-bg-orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(2px);
        }

        .section-brand-atmosphere .brand-bg-orb--one {
            width: 180px;
            height: 180px;
            left: 6%;
            bottom: 12%;
            background: radial-gradient(circle, rgba(161, 61, 219, 0.18), transparent 70%);
            animation: brandOrbFloatA 10s ease-in-out infinite;
        }

        .section-brand-atmosphere .brand-bg-orb--two {
            width: 120px;
            height: 120px;
            right: 28%;
            top: 18%;
            background: radial-gradient(circle, rgba(113, 186, 255, 0.22), transparent 70%);
            animation: brandOrbFloatB 12s ease-in-out infinite;
        }

        .section-brand-atmosphere--mirror .brand-bg-orb--one {
            left: auto;
            right: 8%;
            bottom: 18%;
            animation-delay: -2s;
        }

        .section-brand-atmosphere--mirror .brand-bg-orb--two {
            right: auto;
            left: 30%;
            top: 22%;
            animation-delay: -4s;
        }

        .section-brand-atmosphere .brand-bg-glow {
            position: absolute;
            left: 0;
            top: 18%;
            width: 4px;
            height: 42%;
            border-radius: 999px;
            background: linear-gradient(180deg, transparent, var(--primary), var(--secondary), transparent);
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
            animation: brandGlowPulse 6s ease-in-out infinite;
        }

        .section-brand-atmosphere--mirror .brand-bg-glow {
            left: auto;
            right: 0;
        }

        .section-brand-atmosphere > .container {
            position: relative;
            z-index: 1;
        }

        @keyframes brandMeshDrift {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(3%, -2%, 0) scale(1.05); }
            100% { transform: translate3d(-2%, 3%, 0) scale(1.02); }
        }

        @keyframes brandDotsDrift {
            from { transform: translate3d(0, 0, 0); }
            to { transform: translate3d(-28px, -28px, 0); }
        }

        @keyframes brandRingPulse {
            0%, 100% { transform: scale(1); opacity: 0.85; }
            50% { transform: scale(1.08); opacity: 1; }
        }

        @keyframes brandOrbFloatA {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            33% { transform: translate3d(18px, -22px, 0) scale(1.08); }
            66% { transform: translate3d(-12px, -8px, 0) scale(0.96); }
        }

        @keyframes brandOrbFloatB {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            40% { transform: translate3d(-20px, 16px, 0) scale(1.1); }
            70% { transform: translate3d(14px, -18px, 0) scale(0.94); }
        }

        @keyframes brandGlowPulse {
            0%, 100% { opacity: 0.28; transform: scaleY(1); }
            50% { opacity: 0.55; transform: scaleY(1.08); }
        }

        @media (prefers-reduced-motion: reduce) {
            .section-brand-atmosphere .brand-bg-mesh,
            .section-brand-atmosphere::before,
            .section-brand-atmosphere::after,
            .section-brand-atmosphere .brand-bg-orb--one,
            .section-brand-atmosphere .brand-bg-orb--two,
            .section-brand-atmosphere .brand-bg-glow {
                animation: none;
            }
        }

        /* === Sección Planes: título + reveal === */
        #planes.section-planes-reveal {
            overflow: hidden;
        }

        #planes .planes-title {
            color: #111;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-transform: none;
            opacity: 0;
            transform: translateY(28px);
            transition:
                opacity 1.7s cubic-bezier(0.22, 1, 0.36, 1),
                transform 1.7s cubic-bezier(0.22, 1, 0.36, 1);
        }

        #planes .planes-subtitle {
            opacity: 0;
            transform: translateY(20px);
            transition:
                opacity 1.5s cubic-bezier(0.22, 1, 0.36, 1) 0.25s,
                transform 1.5s cubic-bezier(0.22, 1, 0.36, 1) 0.25s;
        }

        #planes .planes-keyword {
            display: inline-block;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: linear-gradient(
                110deg,
                #A13DDB 0%,
                #71BAFF 45%,
                #A13DDB 100%
            );
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
            filter: blur(0.15px);
            text-shadow: none;
            animation:
                planesKeywordFlow 9s ease-in-out infinite,
                planesKeywordSoftBlur 7s ease-in-out infinite;
            will-change: background-position, filter, transform;
        }

        #planes .service-card {
            opacity: 0;
            transform: translateY(40px) scale(0.98);
            filter: blur(4px);
            transition:
                opacity 1.6s cubic-bezier(0.22, 1, 0.36, 1),
                transform 1.6s cubic-bezier(0.22, 1, 0.36, 1),
                filter 1.6s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.35s ease;
        }

        #planes .service-card:nth-child(1) { transition-delay: 0.2s; }
        #planes .service-card:nth-child(2) { transition-delay: 0.4s; }
        #planes .service-card:nth-child(3) { transition-delay: 0.6s; }

        #planes.is-visible .planes-title,
        #planes.is-visible .planes-subtitle,
        #planes.is-visible .service-card {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0);
        }

        #planes.is-visible .planes-title {
            filter: none;
        }

        @keyframes planesKeywordFlow {
            0% {
                background-position: 0% 50%;
                transform: translateY(0);
            }
            50% {
                background-position: 100% 50%;
                transform: translateY(-1px);
            }
            100% {
                background-position: 0% 50%;
                transform: translateY(0);
            }
        }

        @keyframes planesKeywordSoftBlur {
            0%, 100% {
                filter: blur(0.1px);
                opacity: 0.96;
            }
            50% {
                filter: blur(0.55px);
                opacity: 0.9;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #planes .planes-title,
            #planes .planes-subtitle,
            #planes .service-card {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
            }

            #planes .planes-keyword {
                animation: none;
                filter: none;
                opacity: 1;
                background-position: 40% 50%;
            }
        }


        /* === FOOTER === */
        .footer {
            background-color: var(--primary);
            color: white;
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer p {
            margin: 0;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .footer-social {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
        }

        .footer-social a {
            color: white;
            font-size: 1rem;
            transition: var(--transition);
        }

        .footer-social a:hover {
            color: var(--light-blue);
            transform: scale(1.2);
        }

        .highlight {
            font-size: 3rem;
            font-weight: 600;
            color: var(--secondary);
            text-underline-offset: 6px;
            text-decoration-thickness: 3px;
        }


        /* === ANIMACIONES === */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            opacity: 0;
            animation: fadeIn 0.6s ease forwards;
        }

        /* === RESPONSIVE: Breakpoints === */

        /* Mostrar hamburguesa y ocultar menú desktop en móviles */
        @media (max-width: 768px) {
            .menu-desktop {
                display: none;
            }

            .menu-mobile {
                display: block;
            }

            .social-icons {
                bottom: 0.8rem;
                left: 0.8rem;
            }

            .social-icons a {
                font-size: 1rem;
            }

            .logo img {
                height: 32px;
            }

            .logo-placeholder {
                height: 32px;
                width: 32px;
                font-size: 1rem;
            }

            .section-nosotros h2,
            .section-planes h2 {
                font-size: 2.1rem;
            }

            .section-nosotros h2 .highlight {
                font-size: 2.35rem;
            }

            .section-nosotros p {
                font-size: 1.1rem;
            }

            .plan-card {
                max-width: 300px;
            }
        }

        @media (max-width: 480px) {
            .mobile-menu-panel a {
                font-size: 1.2rem;
                padding: 0.9rem;
            }

            .section-nosotros h2 {
                font-size: 1.95rem;
            }

            .section-nosotros h2 .highlight {
                font-size: 2.15rem;
            }

            .section-planes h2 {
                font-size: 1.7rem;
            }
        }

        /* === BOTÓN EN SECCIÓN NOSOTROS === */
        .btn-nosotros {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            background-color: var(--primary);
            color: white;
            margin-top: 4rem;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 30px;
            /* Bordes redondeados */
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(45, 137, 202, 0.2);
            transform: translateY(0);
        }

        .btn-nosotros:hover {
            transform: translateY(-3px);
            /* Eleva el botón */
            box-shadow: 0 7px 15px rgba(45, 137, 202, 0.35);
            background-color: var(--secondary);
            /* Cambia a azul más brillante */
            scale: 1.05;
            /* Aumento sutil */
        }

        /* === INDICADOR DE SCROLL === */
        .scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            color: white;
            font-size: 1.5rem;
            text-align: center;
            opacity: 0.9;
            animation: bounce 2s infinite;
        }


        .scroll-indicator {
            bottom: 1.5rem;
            font-size: 1.3rem;
        }


        /* Responsive para móviles */
        @media (max-width: 480px) {
            .btn-nosotros {
                padding: 0.7rem 1.5rem;
                font-size: 0.95rem;
            }

            .btn-nosotros:hover {
                scale: 1.03;
                transform: translateY(-2px);
            }

        }

        .menu-style a:hover {
            transform: none;
        }

        .menu-style a:hover span {
            text-shadow: 0 0 10px rgba(132, 211, 246, 0.6), 0 0 20px rgba(132, 211, 246, 0.3);
            color: var(--light-blue);
        }

        /* === TEXTO CENTRADO SOBRE EL VIDEO === */
        .text-center-full {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            padding: 2rem;
            text-align: center;
        }

        .main-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 300;
            letter-spacing: -0.5px;
            line-height: 1.4;
            max-width: 1000px;
            text-shadow:
                0 2px 8px rgba(0, 0, 0, 0.55),
                0 8px 28px rgba(0, 0, 0, 0.45),
                0 0 40px rgba(5, 47, 96, 0.35);
        }

        .main-title-line {
            display: block;
            opacity: 0;
            filter: blur(14px);
            transform: translateY(28px) scale(0.96);
            clip-path: inset(0 0 100% 0);
            animation: heroTitleReveal 1.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .main-title-line:nth-child(2) {
            animation-delay: 0.45s;
        }

        @keyframes heroTitleReveal {
            0% {
                opacity: 0;
                filter: blur(14px);
                transform: translateY(28px) scale(0.96);
                clip-path: inset(0 0 100% 0);
            }
            55% {
                filter: blur(2px);
            }
            100% {
                opacity: 1;
                filter: blur(0);
                transform: translateY(0) scale(1);
                clip-path: inset(0 0 0 0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .main-title-line {
                opacity: 1;
                filter: none;
                transform: none;
                clip-path: none;
                animation: none;
            }
        }

        /* === RESPONSIVE: Ajuste en móviles === */
        @media (max-width: 768px) {
            .main-title {
                font-size: 2rem;
                padding: 0 1rem;
            }
        }

        @media (max-width: 480px) {
            .main-title {
                font-size: 1.6rem;
                line-height: 1.3;
            }
        }

        .highlightText {
            font-weight: bold;
            /* Negrita */
            font-style: italic;
            /* Cursiva */
            background: none;
            /* Sin fondo, para mantenerlo minimalista */
            color: inherit;
            /* Mantiene el color del texto original */
            padding: 0;
            /* Sin relleno extra */
            margin: 0;
            /* Sin márgenes */
            letter-spacing: -0.5px;
            /* Ajuste sutil para mejorar la legibilidad (opcional) */
            font-family: inherit;
            /* Usa la misma fuente que el texto padre */

            /* Sombra externa sutil — clave para el efecto minimalista */
            text-shadow:
                0 1px 2px rgba(0, 0, 0, 0.08),
                0 0 1px rgba(0, 0, 0, 0.05);


        }

        /* === ESTILO ESPECIAL PARA LOS PLANES === */

        .menu-desktop .menu-agent {
            color: var(--bg-light) !important;
            position: relative;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu-desktop .menu-agent::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 8px;
            border: 2px solid transparent;
            background: linear-gradient(90deg, var(--primary), var(--secondary)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .menu-desktop .menu-agent:hover {
            color: white !important;
            background-color: var(--primary);
            transform: scale(1.1) translateY(-3px);
        }

        .menu-desktop .menu-agent:hover::before {
            border-color: var(--secondary);
            background: linear-gradient(90deg, var(--secondary), var(--light-blue)) border-box;
        }


        /* === ESTILO ESPECIAL PARA "PORTAL DEL PACIENTE" === */
        .card {
            background-color: white;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--light-blue);
        }

        .highlight-card {
            background-color: var(--highlight-bg);
            border: 3px solid var(--highlight-border);
            box-shadow: 0 10px 20px 0 var(--highlight-shadow);
            transform: scale(1.05);
        }

        .highlight-card .btn-primary {
            background-color: var(--light-blue);
        }

        .highlight-card .btn-primary:hover {
            background-color: var(--primary);
        }

        .feature-icon {
            color: var(--primary);
        }

        /* Estilo personalizado para el iframe para asegurar el aspecto 16:9 en cualquier ancho */
        .map-container {
            position: relative;
            width: 100%;
            /* Altura del 50% del viewport height para que el mapa sea prominente */
            height: 50vh;
            overflow: hidden;
            border-radius: 0.5rem;
            /* Bordes redondeados */
        }

        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }






        .testimonial-section {
            background: linear-gradient(135deg, rgba(5, 47, 96, 0.03) 0%, rgba(232, 235, 234, 0.8) 100%);
            position: relative;
            overflow: hidden;
        }

        .testimonial-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="%23052F60" fill-opacity="0.05" d="M0,128L48,117.3C96,107,192,85,288,106.7C384,128,480,192,576,192C672,192,768,128,864,122.7C960,117,1056,171,1152,197.3C1248,224,1344,224,1392,224L1440,224L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
        </svg>'); */
            background-size: cover;
            background-position: bottom;
            opacity: 0.15;
            z-index: 1;
        }


        .testimonial-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(5, 47, 96, 0.08);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(232, 235, 234, 0.8);
        }

        .testimonial-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(5, 47, 96, 0.15);
        }

        .testimonial-card::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 20px;
            font-family: Arial, sans-serif;
            font-size: 100px;
            color: #5488AE;
            opacity: 0.1;
            line-height: 1;
        }

        .quote-icon {
            color: #5488AE;
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .testimonial-avatar {
            width: 70px;
            height: 70px;
            border: 3px solid #E8EBEA;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .map-pin {
            width: 24px;
            height: 24px;
            background: #4A8982;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            position: relative;
        }

        .map-pin::after {
            content: "";
            position: absolute;
            top: 8px;
            left: 8px;
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }

        .glide__slide {
            padding: 20px 10px;
        }

        .stats-card {
            background: linear-gradient(135deg, #57cfff 0%, #305B93 100%);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(5, 47, 96, 0.15);
        }

        .world-icon {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .world-icon svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            fill: none;
            stroke: #5488AE;
            stroke-width: 2;
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: draw 8s linear forwards infinite;
        }

        @keyframes draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float-delay {
            animation: float 6s ease-in-out infinite 1.5s;
        }

        .animate-float-delay-2 {
            animation: float 6s ease-in-out infinite 3s;
        }

        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }



        .e-card {
            margin: 100px auto;
            background: transparent;
            box-shadow: 0px 8px 28px -9px rgba(0, 0, 0, 0.45);
            position: relative;
            width: 240px;
            height: 330px;
            border-radius: 16px;
            overflow: hidden;
        }

        .wave {
            position: absolute;
            width: 540px;
            height: 700px;
            opacity: 0.6;
            left: 0;
            top: 0;
            margin-left: -50%;
            margin-top: -70%;
            background: linear-gradient(744deg, #00ff80, #009933 60%, #00cc44);
        }

        .icon {
            width: 3em;
            margin-top: -1em;
            padding-bottom: 1em;
        }

        .infotop {
            text-align: center;
            font-size: 20px;
            position: absolute;
            top: 4em;
            left: 0;
            right: 0;
            color: rgb(255, 255, 255);
            font-weight: 600;
        }

        .name {
            font-size: 14px;
            font-weight: 100;
            position: relative;
            top: 1em;
            text-transform: lowercase;
        }

        .wave:nth-child(2),
        .wave:nth-child(3) {
            top: 210px;
        }

        .playing .wave {
            border-radius: 40%;
            animation: wave 3000ms infinite linear;
        }

        .wave {
            border-radius: 40%;
            animation: wave 55s infinite linear;
        }

        .playing .wave:nth-child(2) {
            animation-duration: 4000ms;
        }

        .wave:nth-child(2) {
            animation-duration: 50s;
        }

        .playing .wave:nth-child(3) {
            animation-duration: 5000ms;
        }

        .wave:nth-child(3) {
            animation-duration: 45s;
        }

        @keyframes wave {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* -------------------------------------- */
        /* ESTILOS ESPECÍFICOS DEL FEED DE INSTAGRAM */
        /* -------------------------------------- */

        .instagram-post-card {
            position: relative;
            overflow: hidden;
            display: block; /* Asegura que el enlace ocupe el espacio */
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
            cursor: pointer;
        }

        /* Asegura que la imagen sea un cuadrado perfecto */
        .aspect-square-custom {
            aspect-ratio: 1 / 1;
        }

        .instagram-post-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Transición suave para el zoom al hacer hover */
            transition: transform 0.4s ease;
        }

        /* Overlay - Simula la interacción de Instagram */
        .instagram-post-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Fondo semi-transparente */
            opacity: 0; /* Inicialmente invisible */
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            font-size: 1.125rem; /* text-lg */
            font-weight: 600; /* semibold */
            transition: opacity 0.3s ease;
            backdrop-filter: blur(2px); /* Efecto moderno y vanguardista */
            -webkit-backdrop-filter: blur(2px); /* Compatibilidad con Safari */
        }

        /* Estado Hover/Focus: Aparece el Overlay y hace zoom a la imagen */
        .instagram-post-card:hover .instagram-post-overlay,
        .instagram-post-card:focus .instagram-post-overlay {
            opacity: 1;
        }

        .instagram-post-card:hover img,
        .instagram-post-card:focus img {
            transform: scale(1.05); /* Zoom ligero de la imagen */
        }

        /* Estilo para el ícono 'Ver Post' (simulación de Likes/Comments) */
        .instagram-post-overlay svg {
        /* Usaremos un ícono de flecha simple por defecto, pero se puede simular Likes/Comments */
            transform: rotate(90deg);
        }

        /* Estilo para el botón CTA con sombra suave */
        .theme-btn {
            box-shadow: 0 10px 15px -3px rgba(29, 78, 216, 0.3), 0 4px 6px -4px rgba(29, 78, 216, 0.3);
        }


    </style>

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <!-- Glide.js para el carrusel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.2.0/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.2.0/css/glide.theme.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.2.0/glide.min.js"></script>


    <!-- Configuración personalizada de Tailwind para usar las variables CSS -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    , }
                    , colors: {
                        // Mapeo de variables a nombres de clases de Tailwind
                        'theme-primary': 'var(--primary)', // #A13DDB (Púrpura)
                        'theme-accent': 'var(--light-blue)', // #096FFF (Azul Brillante)
                        'bg-body': 'var(--bg-lighter)', // #F6F6F7
                        'bg-card': 'var(--bg-light)', // #EFEFEF
                        'footer-dark': 'var(--footer-dark-bg)', // #1A112A (Fondo Oscuro Personalizado)
                        'text-dark': 'var(--text-dark)', // #333
                    }
                }
            }
        }

    </script>

    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    @fluxAppearance

</head>
<body>

    <!-- 1. Sección Video Fullscreen -->
    <section class="fullscreen-video" id="home">
        <!-- ATENCIÓN: Se usó un placeholder para el video, reemplace con la ruta real de Laravel si es necesario -->
        <video autoplay muted loop playsinline>
            <source src="{{ asset('video/videoDos.mp4') }}" type="video/mp4">
            Tu navegador no soporta video.
        </video>
        <div class="overlay"></div>

        <!-- ✅ TEXTO CENTRADO SOBRE EL VIDEO -->
        <div class="text-center-full">
            <h1 class="main-title">
                <span class="main-title-line">{{ $setting->web_sectionOne_title }}</span>
                <span class="main-title-line">{{ $setting->web_sectionOne_title_ln_2 }}</span>
            </h1>
        </div>


        <!-- Logo -->
        <img src="{{ asset('storage/'.$setting->web_headerLogo) }}" alt="Logo Vive Plus" class="logo">



        <!-- Menú desplegable - Esquina superior izquierda con iconos y glow -->
        <div class="absolute top-8 left-6 z-30">
            <!-- Botón con ícono + texto "Menú" -->
            {{-- <button @click="open = !open" class="flex items-center space-x-2 px-4 py-2 rounded-full bg-black bg-opacity-30 backdrop-blur-sm border border-white border-opacity-20 hover:bg-opacity-50 transition-all duration-200 group focus:outline-none text-white text-sm font-medium" aria-label="Menú">
                <div class="flex space-x-1">
                    <span class="block h-1 w-5 bg-white opacity-70 group-hover:opacity-100 transition"></span>
                    <span class="block h-1 w-5 bg-white opacity-70 group-hover:opacity-100 transition"></span>
                </div>
                <span>Menú Comercial</span>
            </button> --}}

            <!-- Dropdown con iconos y efecto glow -->
            {{-- <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="origin-top-left absolute mt-2 w-52 rounded-xl shadow-lg bg-black bg-opacity-20 backdrop-blur-sm border border-white border-opacity-20 hover:bg-opacity-200 overflow-hidden">
                <div class="py-1 text-sm text-gray-200">
                    <!-- Item 1: Panel Principal -->
                    <a href="https://integracorp.tudrgroup.com/master" class="flex items-center px-4 py-3 hover:bg-white hover:bg-opacity-10 transition duration-200 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        <span class="group-hover:text-white transition">AGENCIA MASTER</span>
                        <!-- Efecto glow al hacer hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-transparent opacity-0 group-hover:opacity-100 blur-sm rounded-xl pointer-events-none"></div>
                    </a>
                    <!-- Item 2: General -->
                    <a href="https://integracorp.tudrgroup.com/general" class="flex items-center px-4 py-3 hover:bg-white hover:bg-opacity-10 transition duration-200 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                        <span class="group-hover:text-white transition">AGENCIA GENERAL</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-transparent opacity-0 group-hover:opacity-100 blur-sm rounded-xl pointer-events-none"></div>
                    </a>
                    <!-- Item 2: Agentes -->
                    <a href="https://integracorp.tudrgroup.com/agents" class="flex items-center px-4 py-3 hover:bg-white hover:bg-opacity-10 transition duration-200 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="group-hover:text-white transition">AGENTE</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-transparent opacity-0 group-hover:opacity-100 blur-sm rounded-xl pointer-events-none"></div>
                    </a>
                </div>
            </div> --}}
            <flux:navbar class="hero-nav-links">
                <flux:navbar.item href="https://vivepluss.com/viveadmin" icon="home">VivePlusAdmin</flux:navbar.item>
                <flux:navbar.item href="https://vivepluss.com/viveadmin" icon="puzzle-piece">Agencias</flux:navbar.item>
                <flux:navbar.item href="https://tudrenviajes.xyz/app/pages/login.php" icon="user">Asistencia en Viajes</flux:navbar.item>
            </flux:navbar>
        </div>




        <!-- Menú Desktop (solo en pantallas grandes) -->
        {{-- <nav class="menu-desktop menu-style">
            <ul>
                <li><a href="#">Inicio</a></li>
                <li><a href="{{ route('inConstruccion') }}">Nosotros</a></li>
        <li><a href="{{ route('inConstruccion') }}">Contáctanos</a></li>
        <li><a href="https://integracorp.tudrgroup.com/agents" target="_blank" class="menu-agent">Portal del Agente</a></li>
        <li><a href="https://integracorp.tudrgroup.com/master" target="_blank" class="menu-agent">Portal Agencia Master</a></li>
        <li><a href="https://integracorp.tudrgroup.com/general" target="_blank" class="menu-agent">Portal Agencia General</a></li>
        </ul>
        </nav> --}}

        <!-- Menú Hamburguesa (solo en móviles) -->
        <div class="menu-mobile" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Panel Móvil Desplegable -->
        <div class="mobile-menu-panel" id="mobile-menu">
            <div class="close-menu" id="close-menu">
                <i class="fas fa-times"></i>
            </div>
            <ul>
                {{-- <li><a href="#home" onclick="closeMobileMenu()"><span>Inicio</span></a></li>
                <li><a href="{{ route('inConstruccion') }}" onclick="closeMobileMenu()"><span>Nosotros</span></a></li>
                <li><a href="{{ route('inConstruccion') }}" onclick="closeMobileMenu()"><span>Contáctanos</span></a></li>
                <li><a href="https://integracorp.tudrgroup.com/agents" onclick="closeMobileMenu()" class="menu-agent"><span>Portal del Agente</span></a></li>
                <li><a href="https://integracorp.tudrgroup.com/master" onclick="closeMobileMenu()" class="menu-agent"><span>Portal Agencia Master</span></a></li>
                <li><a href="https://integracorp.tudrgroup.com/general" onclick="closeMobileMenu()" class="menu-agent"><span>Portal Agencia General</span></a></li> --}}
            </ul>
        </div>

        <!-- Redes sociales -->
        <div class="social-icons">
            @if(! empty($setting->web_icons_redSocial))
            @foreach ($setting->web_icons_redSocial as $red)
            <a href="{{ $socialLinks[$red] ?? '#' }}" @if(($socialLinks[$red] ?? '#') !== '#') target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $socialLabels[$red] ?? 'Red social' }}"><i class="{{ $red }}"></i></a>
            @endforeach
            @endif
        </div>

        <!-- Indicador de scroll -->
        <a href="#nosotros" class="scroll-indicator" aria-label="Ir a Sobre Nosotros">
            <i class="fas fa-chevron-down"></i><br>
        </a>

    </section>

    <!-- 2. Sección ¿Quiénes Somos? -->
    <section id="nosotros" class="section-nosotros" aria-labelledby="nosotros-heading">
        <div class="nosotros-bridge" aria-hidden="true">
            <div class="wave-layer wave-layer--back">
                {{-- Tres segmentos idénticos = bucle sin corte --}}
                @for ($i = 0; $i < 3; $i++)
                <svg viewBox="0 0 1200 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="rgba(161, 61, 219, 0.28)" d="M0,0 L0,48 Q150,92 300,48 T600,48 T900,48 T1200,48 L1200,0 Z"></path>
                </svg>
                @endfor
            </div>
            <div class="wave-layer wave-layer--front">
                @for ($i = 0; $i < 3; $i++)
                <svg viewBox="0 0 1200 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="rgba(113, 186, 255, 0.45)" d="M0,0 L0,58 Q150,98 300,58 T600,58 T900,58 T1200,58 L1200,0 Z"></path>
                    <path fill="#F6F6F7" d="M0,72 Q150,108 300,72 T600,72 T900,72 T1200,72 L1200,120 L0,120 Z"></path>
                </svg>
                @endfor
            </div>
        </div>
        <div class="nosotros-content">
            <h2 id="nosotros-heading">{{ $setting->web_nosotrosTitle_parteIzquierda }} <span class="highlight">{{ $setting->web_nosotrosTitle_parteDerecha }}</span></h2>
            <span class="nosotros-accent" aria-hidden="true"></span>
            <p class="text-gray-600 text-xl leading-relaxed card-effect p-6 rounded-xl border border-gray-100 bg-background-light">
                {{ $setting->web_nosotros }}
            </p>
        </div>
    </section>

    <!-- Sección 1: Misión (Texto Izquierda, Imagen Derecha) -->
    <section id="mision" class="section-brand-atmosphere reveal-on-scroll min-screen-height flex items-center justify-center p-8 md:p-16">
        <div class="brand-bg-mesh" aria-hidden="true"></div>
        <span class="brand-bg-orb brand-bg-orb--one" aria-hidden="true"></span>
        <span class="brand-bg-orb brand-bg-orb--two" aria-hidden="true"></span>
        <span class="brand-bg-glow" aria-hidden="true"></span>
        <div class="container mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <!-- Columna Izquierda: Misión -->
            <div class="space-y-6 lg:order-1 order-2 reveal-from-left">
                {{-- <span class="text-secondary-gold text-lg font-semibold tracking-wider uppercase">Nuestro Propósito</span> --}}
                <h2 class="reveal-title text-4xl md:text-5xl font-extrabold text-[#71BAFF] leading-tight">Misión</h2>
                <p class="reveal-text text-gray-600 text-lg leading-relaxed card-effect p-6">
                    {{ $setting->web_mision }}
                </p>
            </div>

            <!-- Columna Derecha: Imagen de Misión -->
            <div class="reveal-from-right lg:order-2 order-1 rounded-xl overflow-hidden shadow-2xl">
                <img src="{{ asset('storage/'.$setting->web_imageMision) }}" alt="Representación de la Misión: Foco en el cliente y soluciones tecnológicas." class="w-full h-full object-cover rounded-xl transform hover:scale-[1.01] transition duration-500 ease-in-out" onerror="this.onerror=null; this.src='https://placehold.co/800x600/1e40af/ffffff?text=Imagen%20de%20Mision';">
            </div>

        </div>
    </section>

    <!-- Sección 2: Visión (Imagen Izquierda, Texto Derecha) - Invertida -->
    <section id="vision" class="section-brand-atmosphere section-brand-atmosphere--alt section-brand-atmosphere--mirror reveal-on-scroll min-screen-height flex items-center justify-center p-8 md:p-16">
        <div class="brand-bg-mesh" aria-hidden="true"></div>
        <span class="brand-bg-orb brand-bg-orb--one" aria-hidden="true"></span>
        <span class="brand-bg-orb brand-bg-orb--two" aria-hidden="true"></span>
        <span class="brand-bg-glow" aria-hidden="true"></span>
        <div class="container mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <!-- Columna Izquierda: Imagen de Visión -->
            <div class="reveal-from-left lg:order-1 order-1 rounded-xl overflow-hidden shadow-2xl">
                <img src="{{ asset('storage/'.$setting->web_imageVision) }}" alt="Representación de la Visión: Liderazgo y crecimiento global futuro." class="w-full h-full object-cover rounded-xl transform hover:scale-[1.01] transition duration-500 ease-in-out" onerror="this.onerror=null; this.src='https://placehold.co/800x600/d97706/ffffff?text=Imagen%20de%20Vision';">
            </div>

            <!-- Columna Derecha: Visión -->
            <div class="space-y-6 lg:order-2 order-2 reveal-from-right">
                {{-- <span class="text-primary-blue text-lg font-semibold tracking-wider uppercase">Nuestro Sueño Futuro</span> --}}
                <h2 class="reveal-title text-4xl md:text-5xl font-extrabold text-[#71BAFF] leading-tight">Visión</h2>

                <p class="reveal-text text-gray-600 text-lg leading-relaxed card-effect p-6">
                    {{ $setting->web_vision }}
                </p>
                {{-- <div class="pt-4">
                    <a href="#" class="inline-block px-8 py-3 bg-secondary-gold text-white font-medium rounded-lg hover:bg-orange-600 transition duration-300 shadow-lg hover:shadow-xl">Únete a Nosotros</a>
                </div> --}}
            </div>

        </div>
    </section>

    <!-- Sección PLanes -->
    <section id="planes" class="section-planes-reveal reveal-on-scroll py-20 bg-light">
        <div class="container mx-auto px-6">
            <!-- Encabezado -->
            <header class="text-center mb-12">
                <h1 class="planes-title text-4xl sm:text-5xl font-extrabold mb-3">
                    {!! $plansTitleFormatted !!}
                </h1>
                <p class="planes-subtitle text-lg sm:text-xl" style="color: var(--text-light);">
                    {{ $setting->web_plansSubTitle }}
                </p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">

                <div class="service-card bg-white p-8 rounded-xl shadow-[0px_0px_0px_1px_rgba(0,0,0,0.06),0px_1px_1px_-0.5px_rgba(0,0,0,0.06),0px_3px_3px_-1.5px_rgba(0,0,0,0.06),_0px_6px_6px_-3px_rgba(0,0,0,0.06),0px_12px_12px_-6px_rgba(0,0,0,0.06),0px_24px_24px_-12px_rgba(0,0,0,0.06)]">
                    <img src="{{ asset('storage/'. $setting->web_imagePlan_1) }}" alt="Planes con cobertura Mundial" class="rounded-xl mb-5 shadow-[0_2.8px_2.2px_rgba(0,_0,_0,_0.034),_0_6.7px_5.3px_rgba(0,_0,_0,_0.048),_0_12.5px_10px_rgba(0,_0,_0,_0.06),_0_22.3px_17.9px_rgba(0,_0,_0,_0.072),_0_41.8px_33.4px_rgba(0,_0,_0,_0.086),_0_100px_80px_rgba(0,_0,_0,_0.12)]">
                    <h3 class="text-xl font-bold mb-4 text-center">{{ $setting->web_namePlan_1 }}</h3>
                    <p class="mb-6 text-justify">{{ $setting->web_descriptionPlan_1 }}</p>
                    <ul class="mb-6 space-y-2">
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_1_items_1 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_1_items_2 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_1_items_3 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_1_items_4 }}</li>
                    </ul>
                    {{-- <div class="text-2xl font-bold text-secondary">Desde $40/día</div> --}}
                </div>

                <div class="service-card bg-white p-8 rounded-xl shadow-[0px_0px_0px_1px_rgba(0,0,0,0.06),0px_1px_1px_-0.5px_rgba(0,0,0,0.06),0px_3px_3px_-1.5px_rgba(0,0,0,0.06),_0px_6px_6px_-3px_rgba(0,0,0,0.06),0px_12px_12px_-6px_rgba(0,0,0,0.06),0px_24px_24px_-12px_rgba(0,0,0,0.06)]">
                    <img src="{{ asset('storage/'.$setting->web_imagePlan_2) }}" alt="Europa Low Cost" class="rounded-xl mb-5 shadow-[0_2.8px_2.2px_rgba(0,_0,_0,_0.034),_0_6.7px_5.3px_rgba(0,_0,_0,_0.048),_0_12.5px_10px_rgba(0,_0,_0,_0.06),_0_22.3px_17.9px_rgba(0,_0,_0,_0.072),_0_41.8px_33.4px_rgba(0,_0,_0,_0.086),_0_100px_80px_rgba(0,_0,_0,_0.12)]">
                    <h3 class="text-2xl font-bold mb-4 text-center">{{ $setting->web_namePlan_2 }}</h3>
                    <p class="mb-6 text-justify">{{ $setting->web_descriptionPlan_2 }}</p>
                    <ul class="mb-6 space-y-2">
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_2_items_1 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_2_items_2 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_2_items_3 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_2_items_4 }}</li>
                    </ul>
                    {{-- <div class="text-2xl font-bold text-secondary">Desde $55/día</div> --}}
                </div>

                <div class="service-card bg-white p-8 rounded-xl shadow-[0px_0px_0px_1px_rgba(0,0,0,0.06),0px_1px_1px_-0.5px_rgba(0,0,0,0.06),0px_3px_3px_-1.5px_rgba(0,0,0,0.06),_0px_6px_6px_-3px_rgba(0,0,0,0.06),0px_12px_12px_-6px_rgba(0,0,0,0.06),0px_24px_24px_-12px_rgba(0,0,0,0.06)]">
                    <img src="{{ asset('storage/'.$setting->web_imagePlan_3) }}" alt="Plan Local (Venezuela)" class="rounded-xl mb-5 shadow-[0_2.8px_2.2px_rgba(0,_0,_0,_0.034),_0_6.7px_5.3px_rgba(0,_0,_0,_0.048),_0_12.5px_10px_rgba(0,_0,_0,_0.06),_0_22.3px_17.9px_rgba(0,_0,_0,_0.072),_0_41.8px_33.4px_rgba(0,_0,_0,_0.086),_0_100px_80px_rgba(0,_0,_0,_0.12)]">
                    <h3 class="text-xl font-bold mb-4 text-center">{{ $setting->web_namePlan_3 }}</h3>
                    <p class="mb-6 text-justify">{{ $setting->web_descriptionPlan_3 }}</p>
                    <ul class="mb-6 space-y-2">
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_3_items_1 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_3_items_2 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_3_items_3 }}</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-success mr-2"></i> {{ $setting->web_Plan_3_items_4 }}</li>
                    </ul>
                    {{-- <div class="text-2xl font-bold text-secondary">Desde $25/día</div> --}}
                </div>

            </div>
        </div>
    </section>

    <!-- Sección Testimonios Mejorada -->
    <section id="testimonios" class="py-24 testimonial-section parallax" 
        style="background-image: url('https://blog.auna.pe/hubfs/paquete-salud-preventiva.png')">

        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center mb-16">
                <div class="inline-block p-3 rounded-full bg-tertiary bg-opacity-10 mb-6">
                    <i class="fas fa-comments text-tertiary text-3xl"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">Experiencias que inspiran confianza</h2>
                <p class="text-xl max-w-3xl mx-auto mb-6 text-white">Personas como tú comparten sus historias de protección alrededor del mundo</p>

                <div class="w-20 h-1 bg-tertiary mx-auto"></div>
            </div>

            <!-- Elementos decorativos flotantes -->
            <div class="hidden md:block">
                <div class="absolute top-20 left-10 animate-float">
                    <div class="bg-accent rounded-full w-10 h-10 opacity-20"></div>
                </div>
                <div class="absolute top-1/3 right-20 animate-float-delay">
                    <div class="bg-tertiary rounded-full w-8 h-8 opacity-20"></div>
                </div>
                <div class="absolute bottom-40 left-1/4 animate-float-delay-2">
                    <div class="bg-secondary rounded-full w-6 h-6 opacity-20"></div>
                </div>
            </div>

            <!-- Carrusel de testimonios -->
            <div class="glide max-w-6xl mx-auto">
                <div class="glide__track" data-glide-el="track">
                    <ul class="glide__slides">
                        <!-- Testimonio 1 -->
                        <li class="glide__slide">
                            <div class="testimonial-card p-8 h-full">
                                <div class="flex items-start mb-6">
                                    {{-- <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="María López" class="testimonial-avatar rounded-full mr-5"> --}}
                                    <div>
                                        <h4 class="font-bold text-xl">MATEO JOSE PARADA BERMUDEZ</h4>

                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 mr-4">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <div class="map-pin mr-2"></div>
                                                <span>Barcelona, España</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-8 text-lg italic relative">"Siii a través de ustedes la
                                    atención excelente, muy rápida, 5 de puntuación!"</p>

                                <div class="flex items-center">
                                    <div class="bg-light p-3 rounded-lg mr-4">
                                        <i class="fas fa-suitcase-rolling text-tertiary text-xl"></i>
                                    </div>
                                    <div class="text-sm">
                                        <div class="font-semibold">Problema con equipaje</div>
                                        <div class="text-gray-500">Resuelto en 24 horas</div>
                                    </div>
                                </div>
                                <i class="fas fa-quote-right quote-icon"></i>
                            </div>
                        </li>

                        <!-- Testimonio 2 -->
                        <li class="glide__slide">
                            <div class="testimonial-card p-8 h-full">
                                <div class="flex items-start mb-6">
                                    {{-- <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Carlos Mendoza" class="testimonial-avatar rounded-full mr-5"> --}}
                                    <div>
                                        <h4 class="font-bold text-xl">EUKARYS VALERIA
                                            CALZADILLA MARTINEZ</h4>

                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 mr-4">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <div class="map-pin mr-2"></div>
                                                <span>Samora, España</span>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-8 text-lg italic relative">"Excelente 5!"</p>

                                <div class="flex items-center">
                                    <div class="bg-light p-3 rounded-lg mr-4">
                                        <i class="fas fa-stethoscope text-tertiary text-xl"></i>
                                    </div>
                                    <div class="text-sm">
                                        <div class="font-semibold">Emergencia médica</div>
                                        <div class="text-gray-500">Resuelto en 48 horas</div>
                                    </div>
                                </div>
                                <i class="fas fa-quote-right quote-icon"></i>
                            </div>
                        </li>

                        <!-- Testimonio 3 -->
                        <li class="glide__slide">
                            <div class="testimonial-card p-8 h-full">
                                <div class="flex items-start mb-6">
                                    {{-- <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=776&q=80" alt="Ana Rodríguez" class="testimonial-avatar rounded-full mr-5"> --}}
                                    <div>
                                        <h4 class="font-bold text-xl">DAMIAN VIVAS BLASI</h4>

                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 mr-4">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <div class="map-pin mr-2"></div>
                                                <span>Davenport, EEUU</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-8 text-lg italic relative">"Ok, 5 puntos 👍👍👍👍"</p>

                                <div class="flex items-center">
                                    <div class="bg-light p-3 rounded-lg mr-4">
                                        <i class="fas fa-plane text-tertiary text-xl"></i>
                                    </div>
                                    <div class="text-sm">
                                        <div class="font-semibold">Vuelo cancelado</div>
                                        <div class="text-gray-500">Resuelto en 12 horas</div>
                                    </div>
                                </div>
                                <i class="fas fa-quote-right quote-icon"></i>
                            </div>
                        </li>

                        <!-- Testimonio 4 -->
                        <li class="glide__slide">
                            <div class="testimonial-card p-8 h-full">
                                <div class="flex items-start mb-6">
                                    {{-- <img src="https://images.unsplash.com/photo-1552058544-f2b08422138a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=798&q=80" alt="Juan Pérez" class="testimonial-avatar rounded-full mr-5"> --}}
                                    <div>
                                        <h4 class="font-bold text-xl">BETTY MARGARITA
                                            HERNANDEZ DE MARTINEZ</h4>

                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 mr-4">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <div class="map-pin mr-2"></div>
                                                <span>Madrid, España</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-8 text-lg italic relative">"La asistencia fue perfecta ,
                                    yo le diera 10 pero como la puntuación más alta es 5 , tienen 5 🥰"</p>

                                <div class="flex items-center">
                                    <div class="bg-light p-3 rounded-lg mr-4">
                                        <i class="fas fa-hotel text-tertiary text-xl"></i>
                                    </div>
                                    <div class="text-sm">
                                        <div class="font-semibold">Alojamiento alternativo</div>
                                        <div class="text-gray-500">Resuelto en 6 horas</div>
                                    </div>
                                </div>
                                <i class="fas fa-quote-right quote-icon"></i>
                            </div>
                        </li>

                        <!-- Testimonio 5 -->
                        <li class="glide__slide">
                            <div class="testimonial-card p-8 h-full">
                                <div class="flex items-start mb-6">
                                    {{-- <img src="https://images.unsplash.com/photo-1552058544-f2b08422138a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=798&q=80" alt="Juan Pérez" class="testimonial-avatar rounded-full mr-5"> --}}
                                    <div>
                                        <h4 class="font-bold text-xl">JUAN PENAGOS</h4>

                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 mr-4">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <div class="map-pin mr-2"></div>
                                                <span>Orlando, EEUU</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-8 text-lg italic relative">"Muchas gracias cuenten con mi recomendación a todos los
                                    viajeros cercanos"</p>

                                <div class="flex items-center">
                                    <div class="bg-light p-3 rounded-lg mr-4">
                                        <i class="fas fa-hotel text-tertiary text-xl"></i>
                                    </div>
                                    <div class="text-sm">
                                        <div class="font-semibold">Alojamiento alternativo</div>
                                        <div class="text-gray-500">Resuelto en 6 horas</div>
                                    </div>
                                </div>
                                <i class="fas fa-quote-right quote-icon"></i>
                            </div>
                        </li>


                    </ul>
                </div>

                <div class="glide__arrows" data-glide-el="controls">
                    <button class="glide__arrow glide__arrow--left absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-lg hover:bg-light focus:outline-none z-20">
                        <i class="fas fa-chevron-left text-[#71BAFF]"></i>

                    </button>
                    <button class="glide__arrow glide__arrow--right absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-lg hover:bg-light focus:outline-none z-20">
                        <i class="fas fa-chevron-right text-[#71BAFF]"></i>

                    </button>
                </div>
            </div>

        </div>
    </section>


    <!-- SECCIÓN DEL FEED DE INSTAGRAM - Usa var(--bg-light) como fondo -->
    <!-- SECCIÓN DEL FEED DE INSTAGRAM - Usa var(--bg-light) como fondo -->
    <section id="instagram-feed-section" class="w-full theme-bg-light py-16 md:py-20 px-4 md:px-12 lg:px-24">

        <!-- Contenedor central con ancho limitado y centrado -->
        <div class="max-w-7xl mx-auto text-center">

            <!-- Encabezado de la Sección -->
            <div class="mb-12">
                <p class="text-lg font-semibold theme-primary-text uppercase tracking-wider">
                    Conéctate con nosotros
                </p>
                <h2 class="text-4xl md:text-5xl font-extrabold theme-text-dark mt-2">
                    #NuestroContenidoReciente
                </h2>
                <p class="mt-4 text-lg theme-text-light max-w-3xl mx-auto">
                    Síguenos en Instagram para ver lo último en noticias, eventos y detrás de cámaras.
                </p>
            </div>

            <!-- CUERPO DEL FEED: GRID DE PUBLICACIONES -->
            <div id="instagram-posts-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">

                <!-- PUBLICACIÓN 1: Viaje a la Playa -->
                <a href="{{ $instagramUrl }}" target="_blank" class="instagram-post-card rounded-lg shadow-xl overflow-hidden transition-shadow duration-300 hover:shadow-2xl">
                    <img src="https://picsum.photos/600/600?random=101" onerror="this.src='https://placehold.co/600x600/40B7FF/ffffff?text=Playa'" alt="Publicación de Instagram: Playa y Sol" class="aspect-square-custom object-cover">
                    <div class="instagram-post-overlay">
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="ml-2 mr-4 text-xl">1.2K</span>

                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span class="ml-2 text-xl">120</span>
                    </div>
                </a>

                <!-- PUBLICACIÓN 2: Café y Trabajo -->
                <a href="{{ $instagramUrl }}" target="_blank" class="instagram-post-card rounded-lg shadow-xl overflow-hidden transition-shadow duration-300 hover:shadow-2xl">
                    <img src="https://picsum.photos/600/600?random=102" onerror="this.src='https://placehold.co/600x600/D0834B/ffffff?text=Café'" alt="Publicación de Instagram: Café y Laptop" class="aspect-square-custom object-cover">
                    <div class="instagram-post-overlay">
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="ml-2 mr-4 text-xl">890</span>
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span class="ml-2 text-xl">55</span>
                    </div>
                </a>

                <!-- PUBLICACIÓN 3: Arte Callejero (Solo visible en md y más) -->
                <a href="{{ $instagramUrl }}" target="_blank" class="instagram-post-card rounded-lg shadow-xl overflow-hidden hidden md:block transition-shadow duration-300 hover:shadow-2xl">
                    <img src="https://picsum.photos/600/600?random=103" onerror="this.src='https://placehold.co/600x600/3E993E/ffffff?text=Arte'" alt="Publicación de Instagram: Mural" class="aspect-square-custom object-cover">
                    <div class="instagram-post-overlay">
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="ml-2 mr-4 text-xl">2.5K</span>
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span class="ml-2 text-xl">412</span>
                    </div>
                </a>

                <!-- PUBLICACIÓN 4: Tecnología y Código (Solo visible en lg y más) -->
                <a href="{{ $instagramUrl }}" target="_blank" class="instagram-post-card rounded-lg shadow-xl overflow-hidden hidden lg:block transition-shadow duration-300 hover:shadow-2xl">
                    <img src="https://picsum.photos/600/600?random=104" onerror="this.src='https://placehold.co/600x600/000000/ffffff?text=Code'" alt="Publicación de Instagram: Código en Pantalla" class="aspect-square-custom object-cover">
                    <div class="instagram-post-overlay">
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="ml-2 mr-4 text-xl">987</span>
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span class="ml-2 text-xl">23</span>
                    </div>
                </a>

                {{-- <!-- PUBLICACIÓN 5: Comida y Receta -->
                <a href="{{ $instagramUrl }}" target="_blank" class="instagram-post-card rounded-lg shadow-xl overflow-hidden hidden xl:block transition-shadow duration-300 hover:shadow-2xl">
                    <img src="https://picsum.photos/600/600?random=105" onerror="this.src='https://placehold.co/600x600/C51C30/ffffff?text=Comida'" alt="Publicación de Instagram: Plato Gourmet" class="aspect-square-custom object-cover">
                    <div class="instagram-post-overlay">
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="ml-2 mr-4 text-xl">4.1K</span>
                        <svg class="w-6 h-6 instagram-icon-color" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span class="ml-2 text-xl">88</span>
                    </div>
                </a> --}}

            </div>
            <!-- FIN DEL CUERPO DEL FEED -->

            <!-- Botón de CTA (Call to Action) -->
            <div class="mt-12">
                <a href="{{ $instagramUrl }}" target="_blank" class="theme-btn inline-flex items-center px-8 py-3 text-lg font-bold text-white rounded-full transition duration-300 ease-in-out transform hover:scale-105">
                    <!-- Icono de Instagram (SVG simple) -->
                    <svg class="w-6 h-6 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12 8a4 4 0 0 1 4 3.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                    Síguenos en Instagram
                </a>
            </div>

        </div> <!-- Fin del Contenedor Central -->

    </section> <!-- Fin de la Sección Completa -->






    <footer class="site-footer bg-footer-dark text-gray-300 pb-12 md:pb-16 shadow-2xl">
        <div class="footer-bridge" aria-hidden="true">
            <div class="wave-layer wave-layer--back">
                @for ($i = 0; $i < 3; $i++)
                <svg viewBox="0 0 1200 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#EFEFEF" d="M0,0 L0,42 Q150,86 300,42 T600,42 T900,42 T1200,42 L1200,0 Z"></path>
                    <path fill="rgba(161, 61, 219, 0.55)" d="M0,0 L0,52 Q150,96 300,52 T600,52 T900,52 T1200,52 L1200,0 Z"></path>
                </svg>
                @endfor
            </div>
            <div class="wave-layer wave-layer--front">
                @for ($i = 0; $i < 3; $i++)
                <svg viewBox="0 0 1200 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="rgba(113, 186, 255, 0.5)" d="M0,0 L0,60 Q150,100 300,60 T600,60 T900,60 T1200,60 L1200,0 Z"></path>
                    <path fill="#1A112A" d="M0,58 Q150,98 300,58 T600,58 T900,58 T1200,58 L1200,120 L0,120 Z"></path>
                </svg>
                @endfor
            </div>
        </div>
        <div class="container mx-auto px-8 md:px-16">
            <div class="footer-main-grid grid grid-cols-2 md:grid-cols-5 gap-10 pb-10 mb-8">

                <!-- Columna 1: Información de la Empresa -->
                <div class="col-span-2 md:col-span-2 space-y-4">
                    <!-- START: Logo de la Empresa (Sustituido) -->
                    <img src="{{ asset('storage/'.$setting->web_footerLogo) }}" alt="Logo Asistencia Médica" class="h-20 w-auto" onerror="this.onerror=null; this.src='https://placehold.co/200x50/A13DDB/ffffff?text=Logo';">

                    <!-- END: Logo de la Empresa -->
                    <p class="text-gray-400 text-sm max-w-sm">
                        {{ $setting->web_footerLogoText }}
                    </p>
                    <div class="flex space-x-4 pt-2">
                        <!-- Iconos de Redes Sociales: El hover usará el color --light-blue gracias al CSS personalizado -->
                        @if(! empty($setting->web_icons_redSocial))
                        @foreach ($setting->web_icons_redSocial as $red)
                        <a href="{{ $socialLinks[$red] ?? '#' }}" @if(($socialLinks[$red] ?? '#') !== '#') target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $socialLabels[$red] ?? 'Red social' }}"><i class="{{ $red }}"></i></a>
                        @endforeach
                        @endif
                    </div>
                </div>

                <!-- Columna 2: Enlaces Rápidos -->
                <div class="space-y-3">
                    <h4 class="text-lg font-semibold text-white mb-2">Compañía</h4>
                    <ul class="space-y-2 text-sm">
                        <!-- Los hover ahora usan el color de acento brillante -->
                        <li><a href="#" class="text-gray-400 hover:text-theme-accent transition duration-200">Inicio</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-theme-accent transition duration-200">Mision</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-theme-accent transition duration-200">Vision</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-theme-accent transition duration-200">Planes</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Soporte -->
                <div class="space-y-3">
                    <h4 class="text-lg font-semibold text-white mb-2">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-theme-accent transition duration-200">Términos de Uso</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-theme-accent transition duration-200">Política de Privacidad</a></li>
                    </ul>
                </div>

                <!-- Columna 4: Contacto -->
                <div class="space-y-3">
                    <h4 class="text-lg font-semibold text-white mb-2">Contacto</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-theme-primary"></i>
                            <a href="mailto:{{ $contactEmail }}" class="text-gray-400 hover:text-theme-accent transition duration-200">{{ $contactEmail }}</a>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fab fa-whatsapp text-theme-primary"></i>
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-theme-accent transition duration-200">
                                WhatsApp: {{ $contactPhone }}
                            </a>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-map-marker-alt text-theme-primary mt-1"></i>
                            <span class="text-gray-400">{{ $setting->web_footerContactAddress }}</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Derechos de Autor / Copyright -->
            <div class="footer-copy text-center text-gray-500 text-xs pt-2">
                &copy; 2025 INTEGRACORP. Todos los derechos reservados.
            </div>

        </div>
    </footer>



    <!-- JavaScript para el menú hamburguesa -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const closeMenu = document.getElementById('close-menu');

        // Abrir menú
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden'; // Evita scroll de fondo
        });

        // Cerrar menú
        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = ''; // Restaura scroll
        }

        // Agregar listener para cerrar al hacer clic en un enlace
        document.querySelectorAll('.mobile-menu-panel a').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        closeMenu.addEventListener('click', closeMobileMenu);

    </script>

    <script>
        // Array de 12 comentarios positivos
        const positiveComments = [{
                user: "Laura M."
                , title: "Diseñadora UX"
                , comment: "¡El servicio es absolutamente fenomenal! La atención fue rápida y la solución perfecta. Un 10/10."
                , avatarClass: "bg-indigo-500"
            }
            , {
                user: "Javier P."
                , title: "Desarrollador Senior"
                , comment: "Increíblemente satisfecho con la calidad que ofrecen. Sigan así, superaron mis expectativas con creces."
                , avatarClass: "theme-primary-bg"
            }, // Usando primary
            {
                user: "Sofía R."
                , title: "Gerente de Proyectos"
                , comment: "No puedo creer lo fácil y rápido que fue todo el proceso. De verdad, fenomenal y muy eficiente."
                , avatarClass: "bg-pink-500"
            }
            , {
                user: "Andrés B."
                , title: "Emprendedor"
                , comment: "Este es el mejor servicio que he usado en años. Totalmente fenomenal y recomendable a cualquier persona."
                , avatarClass: "bg-sky-500"
            }
            , {
                user: "Carmen D."
                , title: "Consultora Financiera"
                , comment: "Mi experiencia fue fantástica de principio a fin. Todo funcionó a la perfección y sin contratiempos, impecable."
                , avatarClass: "theme-primary-bg"
            }, // Usando primary
            {
                user: "Ricardo G."
                , title: "Analista de Datos"
                , comment: "Totalmente recomendado a mis colegas. Súper profesional, rápido y el resultado fue fenomenal."
                , avatarClass: "bg-red-500"
            }
            , {
                user: "Elena V."
                , title: "Jefa de Operaciones"
                , comment: "Una atención al cliente impecable y el servicio que recibí fue absolutamente fenomenal."
                , avatarClass: "bg-purple-500"
            }
            , {
                user: "Fernando A."
                , title: "Arquitecto de Software"
                , comment: "Simplemente excepcional. El equipo detrás de esto hace un trabajo fenomenal, ¡gracias por la dedicación!"
                , avatarClass: "theme-primary-bg"
            }, // Usando primary
            {
                user: "Marta H."
                , title: "Coach de Negocios"
                , comment: "Me quedé sin palabras por la eficiencia. Sinceramente, la plataforma es fenomenal y muy intuitiva."
                , avatarClass: "bg-teal-500"
            }
            , {
                user: "Daniel Z."
                , title: "Director de TI"
                , comment: "La plataforma es intuitiva, el soporte es rápido y el resultado final es fenomenal. Cinco estrellas en todo."
                , avatarClass: "bg-blue-500"
            }
            , {
                user: "Isabel Q."
                , title: "Investigadora"
                , comment: "No tengo ninguna queja, solo elogios. El servicio es de una calidad fenomenal, superando a la competencia."
                , avatarClass: "bg-gray-500"
            }
            , {
                user: "Héctor N."
                , title: "CEO"
                , comment: "Muy contento con la solución. Ha sido la mejor inversión que hemos hecho en la empresa. Fenomenal."
                , avatarClass: "theme-primary-bg"
            } // Usando primary
        ];

        // Función para generar la tarjeta de comentario HTML
        function generateCommentCard(comment) {
            const initials = comment.user.split(' ').map(n => n[0]).join('.');

            // El icono de quote y el avatar (cuando se usa theme-primary-bg) ahora usan el color principal
            return `
            <div class="comment-card flex flex-col items-center text-center">
                <!-- Icono que usa el color primario -->
                <svg class="w-10 h-10 theme-primary-text mb-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M7.707 3.293a1 1 0 010 1.414L3.414 9H16a1 1 0 110 2H3.414l4.293 4.293a1 1 0 01-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                <p class="text-xl italic text-gray-700 mb-6 leading-relaxed">
                    "${comment.comment}"
                </p>
                <!-- Avatar con colores variados o el color primario del tema -->
                <div class="h-12 w-12 rounded-full ${comment.avatarClass} text-white flex items-center justify-center font-semibold text-lg mb-2 shadow-md">
                    ${initials}
                </div>
                <p class="font-bold text-lg theme-text-dark">${comment.user}</p>
                <p class="text-sm text-gray-500">${comment.title}</p>
            </div>
        `;
        }

        // Función para inyectar todos los comentarios en el DOM
        function renderCommentsToSlider() {
            const list = document.getElementById('comments-list');
            if (!list) return;
            list.innerHTML = positiveComments.map(generateCommentCard).join('');
        }

        // --- Lógica del Slider ---

        document.addEventListener('DOMContentLoaded', () => {
            renderCommentsToSlider(); // Carga los 12 comentarios

            const list = document.getElementById('comments-list');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const indicatorDots = document.getElementById('indicator-dots');
            const sliderWrapper = document.getElementById('testimonial-slider-wrapper');

            // Este slider custom ya no está en el DOM (se usa Glide); salir sin romper otras animaciones
            if (!list || !prevBtn || !nextBtn || !indicatorDots || !sliderWrapper) {
                return;
            }

            const totalComments = positiveComments.length;
            let currentIndex = 0;
            const slideDuration = 5000;
            let intervalId;

            function updateSlider() {
                const offset = -currentIndex * 100;
                list.style.transform = `translateX(${offset}%)`;
                updateIndicators();
            }

            function updateIndicators() {
                indicatorDots.innerHTML = '';
                // Color primario del tema para los indicadores activos
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();

                for (let i = 0; i < totalComments; i++) {
                    const dot = document.createElement('button');
                    dot.classList.add('w-3', 'h-3', 'rounded-full', 'transition-colors', 'duration-300', 'focus:outline-none');
                    dot.setAttribute('aria-label', `Comentario ${i + 1}`);

                    if (i === currentIndex) {
                        dot.style.backgroundColor = primaryColor; // Indicador activo usa --primary
                    } else {
                        dot.classList.add('bg-gray-300', 'hover:bg-gray-400');
                    }

                    dot.addEventListener('click', () => {
                        pauseAutoSlide();
                        currentIndex = i;
                        updateSlider();
                        startAutoSlide();
                    });
                    indicatorDots.appendChild(dot);
                }
            }

            function showPrev() {
                pauseAutoSlide();
                currentIndex = (currentIndex - 1 + totalComments) % totalComments;
                updateSlider();
                startAutoSlide();
            }

            function showNext() {
                pauseAutoSlide();
                currentIndex = (currentIndex + 1) % totalComments;
                updateSlider();
                startAutoSlide();
            }

            function startAutoSlide() {
                if (!intervalId) {
                    intervalId = setInterval(showNext, slideDuration);
                }
            }

            function pauseAutoSlide() {
                if (intervalId) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
            }

            // Asigna los eventos a los botones
            prevBtn.addEventListener('click', showPrev);
            nextBtn.addEventListener('click', showNext);

            // Pausa/Reanuda al pasar el ratón para una lectura interactiva
            sliderWrapper.addEventListener('mouseenter', pauseAutoSlide);
            sliderWrapper.addEventListener('mouseleave', startAutoSlide);

            // Inicializa y comienza el carrusel
            updateSlider();
            startAutoSlide();

            // Animación para los testimonios al aparecer en pantalla
            const testimonialCards = document.querySelectorAll('.testimonial-card');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            testimonialCards.forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });

        });

    </script>

    <script>
        // Reveal al scroll: solo cuando la sección entra en viewport
        (function () {
            const sections = [
                document.getElementById('nosotros'),
                ...document.querySelectorAll('.reveal-on-scroll')
            ].filter((el, index, arr) => el && arr.indexOf(el) === index);

            if (!sections.length) return;

            const reveal = (el) => el.classList.add('is-visible');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (prefersReducedMotion || !('IntersectionObserver' in window)) {
                sections.forEach(reveal);
                return;
            }

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.2) {
                        reveal(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: [0.2, 0.35, 0.5],
                rootMargin: '0px 0px -12% 0px'
            });

            sections.forEach((section) => observer.observe(section));
        })();
    </script>

    <script>
        // Inicialización del carrusel
        try {
            if (document.querySelector('.glide') && typeof Glide !== 'undefined') {
                new Glide('.glide', {
                    type: 'carousel'
                    , perView: 1
                    , gap: 30
                    , autoplay: 4000
                    , breakpoints: {
                        768: {
                            perView: 1
                        }
                        , 1024: {
                            perView: 2
                        }
                    }
                }).mount();
            }
        } catch (e) {
            console.warn('Glide no pudo inicializarse:', e);
        }

    </script>



    @fluxScripts

</body>
</html>

