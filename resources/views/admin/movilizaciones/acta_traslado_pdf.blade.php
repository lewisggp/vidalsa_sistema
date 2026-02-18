
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: helvetica; 
            font-size: 10pt; 
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .content {
            text-align: justify;
            margin-bottom: 20px;
        }
        .highlight {
            font-weight: bold;
        }
        
        /* Estilos de Tabla Profesional */
        .data-table { 
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #e6f2ff;
            border: 0.5pt solid #000; 
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 9pt;
            text-transform: uppercase;
        }
        .data-table td {
            border: 0.5pt solid #000; 
            text-align: center;
            padding: 8px;
            font-size: 9pt;
        }

        /* Seccion de Firmas */
        .signatures {
            margin-top: 50px;
            width: 100%;
        }
        .signature-title {
            font-weight: bold;
            font-size: 9pt;
            margin-top: 2px;
        }
        .signature-role {
            font-size: 8pt;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- El Header con Logo, N° Control y Fecha es manejado por ActaTrasladoPDF -->

    <div class="content">
        Por medio del presente documento, el Patio de Máquinas de la CONSTRUCTORA VIDALSA 27, C.A., deja constancia formal de la asignación y el traslado de la siguiente maquinarias y equipos. Los mismos serán movilizados desde su ubicación actual hacia el sitio de ejecución del proyecto 
        <span class="highlight">{{ strtoupper($frenteDestino->NOMBRE_FRENTE) }}</span>.
    </div>

    <!-- EQUIPMENT TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">N°</th>
                <th width="35%">DESCRIPCIÓN / TIPO</th>
                <th width="25%">MARCA</th>
                <th width="30%">SERIAL / PLACA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movilizaciones as $index => $mov)
            <tr>
                <td width="10%">{{ $index + 1 }}</td>
                <td width="35%" style="text-align: left; padding-left: 10px;">{{ strtoupper($mov->equipo->tipo->nombre ?? 'N/A') }}</td>
                <td width="25%">{{ strtoupper($mov->equipo->MARCA ?? 'N/A') }}</td>
                <td width="30%">{{ strtoupper($mov->equipo->SERIAL_CHASIS ?? $mov->equipo->documentacion->PLACA ?? '---') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>




    <!-- SIGNATURES (Una fila horizontal con todos los firmantes) -->
    <table class="signatures" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Responsable 1 -->
            <td width="33%" align="center" valign="bottom">
                @php
                    $resp1Nom = $frenteOrigen->RESP_1_NOM ?? ($frenteOrigen->parent->RESP_1_NOM ?? 'SIN ASIGNAR');
                    $resp1Car = $frenteOrigen->RESP_1_CAR ?? ($frenteOrigen->parent->RESP_1_CAR ?? 'RESPONSABLE');
                @endphp
                <div style="width: 85%; margin: 0 auto; padding-top: 5px; border-top: 1px solid #000;">
                    <div class="signature-title">{{ strtoupper($resp1Nom) }}</div>
                    <div class="signature-role">{{ strtoupper($resp1Car) }}</div>
                </div>
            </td>
            
            <!-- Responsable 2 (si existe) -->
            <td width="34%" align="center" valign="bottom">
                @php
                    $resp2Nom = $frenteOrigen->RESP_2_NOM ?? ($frenteOrigen->parent->RESP_2_NOM ?? null);
                    $resp2Car = $frenteOrigen->RESP_2_CAR ?? ($frenteOrigen->parent->RESP_2_CAR ?? 'RESPONSABLE');
                @endphp
                
                @if($resp2Nom)
                <div style="width: 85%; margin: 0 auto; padding-top: 5px; border-top: 1px solid #000;">
                    <div class="signature-title">{{ strtoupper($resp2Nom) }}</div>
                    <div class="signature-role">{{ strtoupper($resp2Car) }}</div>
                </div>
                @endif
            </td>
            
            <!-- Elaborado Por -->
            <td width="33%" align="center" valign="bottom">
                <div style="width: 85%; margin: 0 auto; padding-top: 5px; border-top: 1px solid #000;">
                    <div class="signature-title">{{ strtoupper(auth()->user()->name ?? 'ALMACÉN / SISTEMA') }}</div>
                    <div class="signature-role">DEPTO. MAQUINARIA</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
