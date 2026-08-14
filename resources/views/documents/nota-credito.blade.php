<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Crédito</title>

    <style>
        @page {
            margin: 40px 50px;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid {{ $primaryColor }};
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            margin: 0;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 11px;
            color: #6b7280;
        }

        .logo {
            width: 90px;
            height: auto;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            margin: 18px 0 6px;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
        }

        table.info td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        table.info td.label {
            width: 38%;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            font-size: 10px;
        }

        table.info td.value {
            color: #111827;
        }

        .credit-summary {
            margin-top: 18px;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid {{ $primaryColor }};
        }

        .credit-summary th {
            background-color: {{ $primaryColor }};
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            padding: 6px;
            text-align: right;
        }

        .credit-summary th:first-child {
            text-align: left;
        }

        .credit-summary td {
            padding: 8px 6px;
            text-align: right;
            font-size: 12px;
        }

        .credit-summary td:first-child {
            text-align: left;
        }

        .credit-summary .highlight {
            font-weight: bold;
            color: {{ $primaryColor }};
        }

        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>Nota de Crédito</h1>
            <p>{{ $whiteCompanyName }}</p>
        </div>
        @if ($logoPath)
            <img class="logo" src="{{ $logoPath }}" alt="">
        @endif
    </div>

    <p class="section-title">Detalle del movimiento</p>
    <table class="info">
        <tr>
            <td class="label">Nro. de nota</td>
            <td class="value">{{ $noteNumber }}</td>
        </tr>
        <tr>
            <td class="label">Fecha</td>
            <td class="value">{{ $date }}</td>
        </tr>
        <tr>
            <td class="label">Afiliación</td>
            <td class="value">{{ $affiliationCode }} — {{ $affiliateName }}</td>
        </tr>
        <tr>
            <td class="label">Plan</td>
            <td class="value">{{ $planDescription }}</td>
        </tr>
        @if ($coverage)
            <tr>
                <td class="label">Cobertura</td>
                <td class="value">{{ $coverageCurrency }} {{ number_format($coverage, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Frecuencia de pago</td>
            <td class="value">{{ $paymentFrequency }}</td>
        </tr>
    </table>

    <p class="section-title">Movimiento de crédito</p>
    <table class="credit-summary">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Crédito asignado a {{ $whiteCompanyName }}</td>
                <td>{{ $currency }} {{ number_format($assignedCredit, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Crédito restante antes de este movimiento</td>
                <td>{{ $currency }} {{ number_format($remainingCreditBefore, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Monto cargado a crédito en esta nota</td>
                <td>- {{ $currency }} {{ number_format($amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="highlight">Crédito restante después de este movimiento</td>
                <td class="highlight">{{ $currency }} {{ number_format($remainingCreditBefore - $amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente por el sistema al aprobar el pago a crédito de la afiliación {{ $affiliationCode }}.</p>
    </div>

</body>
</html>
