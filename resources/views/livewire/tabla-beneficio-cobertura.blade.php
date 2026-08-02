<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
    <div style="/* Contenedor general: se reducen los bordes y el padding exterior //* background-color: #ffffff;border: 1px solid #e5e7eb;border-radius: 0.3rem; */padding: 0.3rem;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);width: 100%;margin: 0;">
        @if ($planId && count($coverages) > 0)

            {{-- Contenedor de la Tabla --}}
            <div style="/* margin-top: 0.3rem; */border: 1px solid #e5e7eb; border-radius: 0.3rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                <table style="width: 100%; /* padding: 0.5rem; */border-collapse: collapse; font-family: 'quicksand', sans-serif; font-size: 0.5rem; /* ¡TAMAÑO DE FUENTE EXTREMADAMENTE PEQUEÑO! */text-align: left; color: {{ $colorPrimary }};table-layout: fixed; ">
                    <thead style="background-color: {{ $colorPrimary }}; color: #ffffff; text-transform: uppercase; font-size: 0.6rem;">
                        <tr>
                            {{-- Primera Columna Fija: Beneficio (30% del ancho) --}}
                            <th scope="col" style="padding: 0.6rem 0.2rem; font-weight: 700; text-align: left;width: 50%; background-color: {{ $colorPrimary }};">
                                {{-- @if($planId == 1)
                                    BENEFICIOS PLAN Esencial
                                @endif --}}
                                @if($planId == 2)
                                    BENEFICIOS PLAN Bienestar
                                @endif
                                @if($planId == 3)
                                    BENEFICIOS PLAN Premium
                                @endif
                            </th>
                            {{-- Columnas Dinámicas: Coberturas (70% del ancho restante) --}}
                            @foreach ($coverages as $coverage)
                                <th scope="col" style="padding: 0.2rem; text-align: center; font-weight: 700; width: {{ $coverageColumnWidth }}%; word-break: break-word; ">
                                    {{ $currency }} {{ number_format($coverage->price, 0, '.', '') }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Filas Dinámicas: Beneficios --}}
                        @foreach ($matrix as $benefitId => $data)
                        <tr style="background-color: #ffffff; border-bottom: 1px solid #f3f4f6; ">
                            {{-- Nombre del Beneficio --}}
                            <th scope="row" style="padding: 0.2rem; font-weight: 500; color: #1f2937; text-align: left;word-break: break-word; ">
                                {{ $data['nombre'] }}
                            </th>
                            {{-- Celdas de Límites de Uso --}}
                            @foreach ($coverages as $coverage)
                            <td style="padding: 0.2rem; text-align: center;justify-content: center; /* Centrado horizontal */align-items: center; /* Centrado vertical */word-break: break-word;">
                                @php
                                    $isNumeric = is_numeric($data['limits'][$coverage->id]);
                                    $color = $isNumeric ? '#2563eb' : '#9ca3af';
                                    $fontWeight = $isNumeric ? '700' : '500';

                                @endphp
                                <span style="color: {{ $color }}; font-weight: {{ $fontWeight }};">
                                    @if($data['limits'][$coverage->id] != 'N/A')
                                        {{ $currency }} {{ number_format($data['limits'][$coverage->id], 0, '.', '') }}
                                    @else
                                    <div style="justify-content: center; /* Centrado horizontal */
                                                align-items: center; /* Centrado vertical */
                                                display: flex;">

                                        <img src="{{ public_path('storage/images-cotizacion/check.png') }}" style="width: 10px; height: auto;"  alt="">

                                    </div>
                                    @endif
                                </span>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($planId)
            <div style="padding: 1rem; text-align: center; color: #9ca3af; font-size: 0.8rem;">
                El Plan seleccionado no tiene coberturas asignadas.
            </div>
        @else
            <div style="padding: 1rem; text-align: center; color: #9ca3af; font-size: 0.8rem;">
                Por favor, selecciona un Plan para ver la matriz de límites.
            </div>
        @endif
    </div>
</div>

