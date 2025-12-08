<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
    <div style="/* Contenedor general: se reducen los bordes y el padding exterior //* background-color: #ffffff;border: 1px solid #e5e7eb;border-radius: 0.3rem; */padding: 0.3rem;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);width: 100%;margin: 0;">
        {{-- Contenedor de la Tabla --}}
        <div style="/* margin-top: 0.3rem; */border: 1px solid #e5e7eb; border-radius: 0.3rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
            <table style="width: 100%; /* padding: 0.5rem; */border-collapse: collapse; font-family: 'quicksand', sans-serif; font-size: 0.5rem; /* ¡TAMAÑO DE FUENTE EXTREMADAMENTE PEQUEÑO! */text-align: left; color: {{ $colorPrimary }};table-layout: fixed; ">
                <thead style="background-color: {{ $colorPrimary }}; color: #ffffff; text-transform: uppercase; font-size: 0.5rem;">
                    <tr>
                        {{-- Primera Columna Fija: Beneficio (50% del ancho) --}}
                        <th scope="col" style="padding: 0.2rem; font-weight: 700; text-align: left; width: 85%; background-color: {{ $colorPrimary }};">
                            BENEFICIOS PLAN Esencial
                        </th>
                        {{-- Columnas Basia:  --}}
                        <th scope="col" style="padding: 0.2rem; text-align: center; font-weight: 700; width: 15%; word-break: break-word; ">
                            
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Filas Dinámicas: Beneficios --}}
                    @foreach ($benefits as $items)
                    <tr style="background-color: #ffffff; border-bottom: 1px solid #f3f4f6; ">
                        {{-- Nombre del Beneficio --}}
                        <th scope="row" style="padding: 0.2rem; font-weight: 500; color: #1f2937; text-align: left;word-break: break-word; ">
                            {{ $items->description }}
                        </th>
                        <th>
                            <img src="{{ public_path('storage/images-cotizacion/check.png') }}" style="width: 10px; height: auto;" alt="">
                        </th>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

