@php
    use App\Models\Configuration;
    // Configuración defensiva para prevenir fallos si el registro no existe
    $defaultConfig = [
        'web_headTitle'             => 'Integracorp | VivePluss',
        'web_headDescription'       => 'Vivepluss: Soluciones de seguros y asistencia.',
        'web_headKeywords'          => 'Integracorp, Seguros, Asistencia, Vivepluss',
        'web_headOpTitle'           => 'VivePluss - Siempre contigo',
        'web_headOpDescription'     => 'Tu plataforma integral de bienestar.',
        'web_headXTitle'            => 'VivePluss',
        'web_headXDescription'      => 'Conoce nuestros planes.',
        'web_sectionOne_title'      => 'Tu tranquilidad es nuestra prioridad.',
        'web_sectionOne_title_ln_2' => 'Descubre el plan perfecto para ti.',
        'web_headerLogo'            => 'images/ViveplussBlanco.png', // Placeholder
        'web_footerCopy'            => 'Integracorp © 2024. Todos los derechos reservados.',
    ];

    // Obtener el registro, o usar un objeto anónimo basado en $defaultConfig si es null
    $setting = Configuration::first() ?? (object) $defaultConfig;

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
        .theme-primary-bg { background-color: var(--primary); }
        .theme-primary-text { color: var(--primary); }
        .theme-bg-light { background-color: var(--bg-light); }
        .theme-text-dark { color: var(--text-dark); }
        .theme-border-gray { border-color: #e5e7eb; } /* Un gris suave */

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
            background-color: var(--light-blue); /* Cambio de color al azul brillante */
            box-shadow: 0 8px 20px var(--highlight-shadow);
            transform: translateY(-2px);
        }

        /* Estilo para el efecto hover de las imágenes */
        .instagram-post-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .instagram-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Capa de superposición de Instagram al pasar el ratón */
        .instagram-post-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(161, 61, 219, 0.6); /* --primary con transparencia */
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .instagram-post-card:hover .instagram-post-overlay {
            opacity: 1;
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
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
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

        /* === SECCIONES === */
        .section-nosotros {
            padding: 4rem 1.5rem;
            background-color: var(--bg-lighter);
            text-align: center;
        }

        .section-nosotros h2 {
            font-size: 2rem;
            font-weight: 300;
            color: var(--primary);
            margin-bottom: 1.2rem;
        }

        .section-nosotros p {
            max-width: 850px;
            margin: 0 auto;
            font-size: 1.2rem;
            line-height: 1.7;
            color: var(--text-light);
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
                font-size: 1.8rem;
            }

            .section-nosotros p {
                font-size: 0.95rem;
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
                font-size: 1.7rem;
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
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 1s ease forwards;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        /* Animación suave de entrada */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
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
            border-radius: 0.5rem; /* Bordes redondeados */
        }
        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        




    </style>

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
                {{ $setting->web_sectionOne_title }}
                <br>
                {{ $setting->web_sectionOne_title_ln_2 }}
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
            <flux:navbar>
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
            @if($setting->web_icons_redSocial != null)
                @foreach ($setting->web_icons_redSocial as $red)
                    <a href="#"><i class="{{ $red }}"></i></a>
                @endforeach
            @endif
        </div>

        <!-- Indicador de scroll -->
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i><br>
        </div>

    </section>

    <!-- 2. Sección ¿Quiénes Somos? -->
    <section id="nosotros" class="section-nosotros">
        <h2>{{ $setting->web_nosotrosTitle_parteIzquierda }} <span class="highlight" style="font-size: 2rem;">{{ $setting->web_nosotrosTitle_parteDerecha }}</span></h2>
        <p class="text-gray-600 text-xl leading-relaxed card-effect p-6 rounded-xl border border-gray-100 bg-background-light">
            {{ $setting->web_nosotros }}
        </p>
    </section>

    <!-- Sección 1: Misión (Texto Izquierda, Imagen Derecha) -->
    <section class="min-screen-height flex items-center justify-center p-8 md:p-16 bg-white">
        <div class="container mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <!-- Columna Izquierda: Misión -->
            <div class="space-y-6 lg:order-1 order-2">
                {{-- <span class="text-secondary-gold text-lg font-semibold tracking-wider uppercase">Nuestro Propósito</span> --}}
                <h2 class="text-4xl md:text-5xl font-extrabold text-[#71BAFF] leading-tight">Misión</h2>
                <p class="text-gray-600 text-lg leading-relaxed card-effect p-6">
                    {{ $setting->web_mision }}                
                </p>
            </div>

            <!-- Columna Derecha: Imagen de Misión -->
            <div class="lg:order-2 order-1 transform hover:scale-[1.01] transition duration-500 ease-in-out rounded-xl overflow-hidden shadow-2xl">
                <img src="{{ asset('storage/'.$setting->web_imageMision) }}" alt="Representación de la Misión: Foco en el cliente y soluciones tecnológicas." class="w-full h-full object-cover rounded-xl" onerror="this.onerror=null; this.src='https://placehold.co/800x600/1e40af/ffffff?text=Imagen%20de%20Mision';">
            </div>

        </div>
    </section>

    <!-- Sección 2: Visión (Imagen Izquierda, Texto Derecha) - Invertida -->
    <section class="min-screen-height flex items-center justify-center p-8 md:p-16 bg-gray-50 border-t border-gray-100">
        <div class="container mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <!-- Columna Izquierda: Imagen de Visión -->
            <div class="lg:order-1 order-1 transform hover:scale-[1.01] transition duration-500 ease-in-out rounded-xl overflow-hidden shadow-2xl">
                <img src="{{ asset('storage/'.$setting->web_imageVision) }}" alt="Representación de la Visión: Liderazgo y crecimiento global futuro." class="w-full h-full object-cover rounded-xl" onerror="this.onerror=null; this.src='https://placehold.co/800x600/d97706/ffffff?text=Imagen%20de%20Vision';">
            </div>

            <!-- Columna Derecha: Visión -->
            <div class="space-y-6 lg:order-2 order-2">
                {{-- <span class="text-primary-blue text-lg font-semibold tracking-wider uppercase">Nuestro Sueño Futuro</span> --}}
                <h2 class="text-4xl md:text-5xl font-extrabold text-[#71BAFF] leading-tight">Visión</h2>

                <p class="text-gray-600 text-lg leading-relaxed card-effect p-6">
                    {{ $setting->web_vision }}
                </p>
                {{-- <div class="pt-4">
                    <a href="#" class="inline-block px-8 py-3 bg-secondary-gold text-white font-medium rounded-lg hover:bg-orange-600 transition duration-300 shadow-lg hover:shadow-xl">Únete a Nosotros</a>
                </div> --}}
            </div>

        </div>
    </section>

    

    <!-- 3. Sección Planes de Salud (Mejorada) -->
    <section class="section-planes" id="planes">
        <div class="w-full max-w-6xl mx-auto">
            <!-- Encabezado -->
            <header class="text-center mb-12">
                <h1 class="text-4xl sm:text-5xl font-extrabold mb-3" style="color: var(--text-dark);">
                    {{ $setting->web_plansTitle }}
                </h1>
                <p class="text-lg sm:text-xl" style="color: var(--text-light);">
                    {{ $setting->web_plansSubTitle }}
                </p>
            </header>

            <!-- Contenedor de las Tarjetas de Precios (Responsive Grid) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Plan 1: Básico -->
                <div class="card p-6 rounded-2xl shadow-lg flex flex-col">
                    <h2 class="text-2xl font-bold mb-2" style="color: var(--text-dark);">{{ $setting->web_namePlan_1 }}</h2>
                    <p class="text-sm mb-4" style="color: var(--text-light);">
                        {{ $setting->web_descriptionPlan_1 }}
                    </p>
                    <div class="text-5xl font-extrabold mb-2" style="color: var(--light-blue);">
                        {{ $setting->web_pricePlan_1 }}
                        <span class="text-xl font-medium" style="color: var(--text-light);">/{{ $setting->web_formaPagoPlan_1 }}</span>
                    </div>
                    <p class="text-xs mb-6" style="color: var(--text-light);">{{ $setting->web_descriptionPricePlan_1 }}</p>
                    <ul class="space-y-3 mb-8 flex-grow">
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Atención Médica Telefónica</span>
                        </li>
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Entrega de Tratamiento Médico a Domicilio</span>
                        </li>
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Monitoreo Telefónico Evolutivo</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Atención Médica Domiciliaria con Tratamiento de Unidosis Incluida</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Laboratorios a Domicilio con fines Diagnósticos</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Imagenología a Domicilio con fines Diagnósticos</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Seguimiento e Interpretación de Resultados</span>
                        </li>
                    </ul>
                    <button class="btn-primary py-3 rounded-xl font-semibold mt-auto">
                        {{ $setting->web_descriptionBottonPlan_1 }}
                    </button>
                </div>

                <!-- Plan 2: Profesional (Destacado) -->
                <div class="card highlight-card p-8 rounded-3xl flex flex-col shadow-2xl z-10">
                    <div class="text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full w-fit mb-4" style="background-color: var(--primary); color: white;">
                        Más Popular
                    </div>
                    <h2 class="text-3xl font-bold mb-2" style="color: var(--text-dark);">{{ $setting->web_namePlan_2 }}</h2>
                    <p class="text-sm mb-4" style="color: var(--text-light);">
                        {{ $setting->web_descriptionPlan_2 }}
                    </p>
                    <div class="text-6xl font-extrabold mb-2" style="color: var(--primary);">
                        {{ $setting->web_pricePlan_2 }}
                        <span class="text-xl font-medium" style="color: var(--text-light);">/{{ $setting->web_formaPagoPlan_2 }}</span>
                    </div>
                    <p class="text-xs mb-6" style="color: var(--text-light);">{{ $setting->web_descriptionPricePlan_2 }}</p>
                    <ul class="space-y-3 mb-8 flex-grow">
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Atención Médica Telefónica</span>
                        </li>
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Entrega de Tratamiento Médico a Domicilio</span>
                        </li>
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Monitoreo Telefónico Evolutivo</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Atención Médica Domiciliaria con Tratamiento de Unidosis Incluida</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Laboratorios a Domicilio con fines Diagnósticos</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Imagenología a Domicilio con fines Diagnósticos</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Seguimiento e Interpretación de Resultados</span>
                        </li>

                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Traslado en Ambulancia urbano en caso de Emergencia</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Consulta ONLINE o Presencial con Médicos Especialistas</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Urgencias Menores en Domicilio o en sitio</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Asistencia Médica por emergencia. (Patologías Listadas)</span>
                        </li>
                    </ul>
                    <button class="btn-primary py-3 rounded-xl font-semibold mt-auto">
                        {{ $setting->web_descriptionBottonPlan_2 }}
                    </button>
                </div>

                <!-- Plan 3: Empresarial -->
                <div class="card p-6 rounded-2xl shadow-lg flex flex-col">
                    <h2 class="text-2xl font-bold mb-2" style="color: var(--text-dark);">
                        {{ $setting->web_namePlan_3 }}
                    </h2>
                    <p class="text-sm mb-4" style="color: var(--text-light);">
                        {{ $setting->web_descriptionPlan_3 }}
                    </p>
                    <div class="text-5xl font-extrabold mb-2" style="color: var(--secondary);">
                        {{ $setting->web_pricePlan_3 }}
                        <span class="text-xl font-medium" style="color: var(--text-light);">/{{ $setting->web_formaPagoPlan_3 }}</span>
                    </div>
                    <p class="text-xs mb-6" style="color: var(--text-light);">{{ $setting->web_descriptionPricePlan_3 }}</p>
                    <ul class="space-y-3 mb-8 flex-grow">
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Atención Médica Telefónica</span>
                        </li>
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Entrega de Tratamiento Médico a Domicilio</span>
                        </li>
                        <li class="flex items-start">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-dark); font-size: 13px;">Monitoreo Telefónico Evolutivo</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Atención Médica Domiciliaria con Tratamiento de Unidosis Incluida</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Laboratorios a Domicilio con fines Diagnósticos</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Imagenología a Domicilio con fines Diagnósticos</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Seguimiento e Interpretación de Resultados</span>
                        </li>

                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Traslado en Ambulancia urbano en caso de Emergencia</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Consulta ONLINE o Presencial con Médicos Especialistas</span>
                        </li>
                        <li class="flex items-start" style="color: var(--text-light);">
                            <span class="feature-icon mr-2 text-sm">&#10003;</span>
                            <span style="color: var(--text-light); font-size: 13px;">Asistencia Médica por Accidente</span>
                        </li>
                    </ul>
                    <button class="btn-primary py-3 rounded-xl font-semibold mt-auto">
                        {{ $setting->web_descriptionBottonPlan_3 }}
                    </button>
                </div>
                
            </div>

            <!-- Pie de página con contacto -->
            <footer class="text-center mt-12 pt-6 border-t" style="border-color: var(--text-light);">
                <p class="text-sm" style="color: var(--text-light);">{{ $setting->web_footerPlans }} <a href="#" class="font-semibold underline" style="color: var(--primary);">{{ $setting->web_footerBottonPlans }}</a>.</p>
            </footer>
        </div>


    </section>

    <section id="comments-section" class="w-full theme-bg-light py-16 px-4 md:px-12 lg:px-24">

        <!-- Contenedor central (Slider) con ancho limitado y centrado -->
        <div id="testimonial-slider-wrapper" class="max-w-6xl mx-auto bg-white shadow-2xl rounded-xl overflow-hidden relative theme-border-gray border">

            <!-- Título de la Sección -->
            <div class="p-6 text-center theme-border-gray border-b">
                <h2 class="text-3xl font-bold theme-text-dark">La Voz de Nuestros Clientes</h2>
                <p class="text-gray-500 mt-1">Más de una docena de usuarios confirman la calidad fenomenal de nuestro servicio.</p>
            </div>

            <!-- Contenedor del Slider con los Comentarios -->
            <div class="slider-container" id="slider-viewport">
                <div class="comments-list" id="comments-list">
                    <!-- Los comentarios serán inyectados aquí por JavaScript -->
                </div>
            </div>

            <!-- Controles de Navegación (Flechas) -->
            <div class="absolute inset-y-0 left-0 flex items-center p-4">
                <button id="prev-btn" class="nav-btn p-3 bg-white/80 hover:bg-white rounded-full shadow-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-primarytailwind focus:ring-opacity-50 theme-text-dark">
                    <!-- Ícono de flecha izquierda -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <div class="absolute inset-y-0 right-0 flex items-center p-4">
                <button id="next-btn" class="nav-btn p-3 bg-white/80 hover:bg-white rounded-full shadow-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-primarytailwind focus:ring-opacity-50 theme-text-dark">
                    <!-- Ícono de flecha derecha -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Indicadores (Dots) -->
            <div id="indicator-dots" class="flex justify-center space-x-2 p-4 theme-border-gray border-t">
                <!-- Los dots se inyectarán aquí con JavaScript -->
            </div>

        </div> <!-- Fin del Contenedor Central -->

    </section> <!-- Fin de la Sección Completa -->

    <!-- SECCIÓN DEL FEED DE INSTAGRAM - Usa var(--bg-light) como fondo -->
    <section id="instagram-feed-section" class="w-full theme-bg-light py-20 px-4 md:px-12 lg:px-24">

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

                <!-- PUBLICACIÓN 1 (Placeholder) -->
                <a href="https://www.instagram.com/tuusuario/" target="_blank" class="instagram-post-card rounded-lg shadow-md hover:scale-105">
                    <img src="https://placehold.co/600x600/A13DDB/ffffff?text=Post+1" alt="Publicación de Instagram 1" class="w-full h-full object-cover rounded-lg aspect-square">
                    <div class="instagram-post-overlay rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5.586L6.707 7.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L11 9.586V4a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Ver Post
                    </div>
                </a>

                <!-- PUBLICACIÓN 2 (Placeholder) -->
                <a href="https://www.instagram.com/tuusuario/" target="_blank" class="instagram-post-card rounded-lg shadow-md hover:scale-105">
                    <img src="https://placehold.co/600x600/71BAFF/ffffff?text=Post+2" alt="Publicación de Instagram 2" class="w-full h-full object-cover rounded-lg aspect-square">
                    <div class="instagram-post-overlay rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5.586L6.707 7.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L11 9.586V4a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Ver Post
                    </div>
                </a>

                <!-- PUBLICACIÓN 3 (Placeholder) -->
                <a href="https://www.instagram.com/tuusuario/" target="_blank" class="instagram-post-card rounded-lg shadow-md hover:scale-105 hidden sm:block">
                    <img src="https://placehold.co/600x600/096FFF/ffffff?text=Post+3" alt="Publicación de Instagram 3" class="w-full h-full object-cover rounded-lg aspect-square">
                    <div class="instagram-post-overlay rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5.586L6.707 7.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L11 9.586V4a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Ver Post
                    </div>
                </a>

                <!-- PUBLICACIÓN 4 (Placeholder) -->
                <a href="https://www.instagram.com/tuusuario/" target="_blank" class="instagram-post-card rounded-lg shadow-md hover:scale-105 hidden lg:block">
                    <img src="https://placehold.co/600x600/A13DDB/ffffff?text=Post+4" alt="Publicación de Instagram 4" class="w-full h-full object-cover rounded-lg aspect-square">
                    <div class="instagram-post-overlay rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5.586L6.707 7.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L11 9.586V4a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Ver Post
                    </div>
                </a>

                <!-- Puedes añadir más publicaciones si lo deseas. -->

            </div>
            <!-- FIN DEL CUERPO DEL FEED -->

            <!-- Botón de CTA (Call to Action) -->
            <div class="mt-12">
                <a href="https://www.instagram.com/tuusuario/" target="_blank" class="inline-flex items-center px-8 py-3 text-lg font-bold text-white rounded-full theme-btn">
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




    <footer class="bg-footer-dark text-gray-300 py-12 md:py-16 border-t-8 border-theme-primary shadow-2xl">
        <div class="container mx-auto px-8 md:px-16">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10 border-b border-gray-700 pb-10 mb-8">

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
                        @if($setting->web_icons_redSocial != null)
                            @foreach ($setting->web_icons_redSocial as $red)
                                <a href="#"><i class="{{ $red }}"></i></a>
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
                            <!-- Usando el color primario (Púrpura) para los iconos -->
                            <i data-lucide="mail" class="w-4 h-4 text-theme-primary"></i>
                            <a href="mailto:info@techsolutions.com" class="text-gray-400 hover:text-theme-accent transition duration-200">{{ $setting->web_footerContactEmail }}</a>

                        </li>
                        <li class="flex items-center space-x-2">
                            <i data-lucide="phone" class="w-4 h-4 text-theme-primary"></i>
                            <span class="text-gray-400">{{ $setting->web_footerContactPhone }}</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-theme-primary mt-1"></i>
                            <span class="text-gray-400">{{ $setting->web_footerContactAddress }}</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Derechos de Autor / Copyright -->
            <div class="text-center text-gray-500 text-xs">
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
        list.innerHTML = positiveComments.map(generateCommentCard).join('');
    }

    // --- Lógica del Slider ---

    document.addEventListener('DOMContentLoaded', () => {
        renderCommentsToSlider(); // Carga los 12 comentarios

        const list = document.getElementById('comments-list');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const indicatorDots = document.getElementById('indicator-dots');
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
        const sliderWrapper = document.getElementById('testimonial-slider-wrapper');
        sliderWrapper.addEventListener('mouseenter', pauseAutoSlide);
        sliderWrapper.addEventListener('mouseleave', startAutoSlide);

        // Inicializa y comienza el carrusel
        updateSlider();
        startAutoSlide();
    });

</script>


@fluxScripts

</body>
</html>
