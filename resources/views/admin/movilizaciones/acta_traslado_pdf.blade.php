<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: helvetica, Arial, sans-serif; 
            font-size: 10pt; 
            color: #000;
            line-height: 1.2;
            margin: 20px;
        }

        /* 1. ENCABEZADO */
        .header-table { width: 100%; border: none; }
        
        .spacer-40 {
            height: 40px;
            font-size: 1px;
            line-height: 40px;
            display: block;
        }

        /* 2. TÍTULO */
        .main-title {
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 15pt;
            text-transform: uppercase;
        }

        .spacer-25 {
            height: 25px;
            font-size: 1px;
            line-height: 25px;
            display: block;
        }

        /* CUERPO DEL TEXTO */
        .content { 
            text-align: justify; 
            margin-bottom: 25px; 
            line-height: 1.5; 
        }

        /* TABLA DE EQUIPOS */
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
        }
        .data-table td {
            border: 0.5pt solid #000; 
            text-align: center;
            padding: 8px;
            font-size: 9pt;
        }

        /* SECCIÓN DE FIRMAS */
        .signatures-container {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-item {
            text-align: center;
            vertical-align: bottom;
        }
        .firm-header { 
            font-weight: bold; 
            font-size: 9pt; 
            padding-bottom: 45px; 
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 85%;
            margin: 0 auto;
            padding-top: 5px;
        }
        .name { font-weight: bold; font-size: 8pt; display: block; }
        .role { font-size: 7pt; font-style: italic; display: block; }

        /* RECEPTOR CENTRADO Y ALINEADO */
        .receptor-table-wrapper {
            width: 100%;
            margin-top: 50px;
        }
        .manual-entry-table {
            margin: 0 auto;
            width: 250px; /* Ancho fijo para asegurar el centrado */
        }
    </style>
</head>
<body>

    <table class="header-table" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="30%">
                <div style="width: 130px; height: 50px; border: 1px dashed #ccc; text-align: center; line-height: 50px; font-size: 8pt;">LOGOTIPO</div>
            </td>
            <td width="70%" style="text-align: right;">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="text-align: right; font-size: 9pt;">
                            FECHA DE EMISIÓN: <strong>{{ date('d/m/Y') }}</strong> &nbsp;&nbsp;&nbsp;&nbsp; N° OPERACIÓN: <strong>{{ str_pad($movilizacion->id ?? 0, 6, '0', STR_PAD_LEFT) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; font-size: 9pt; padding-top: 5px;">
                            EMITIDO POR: <strong>{{ strtoupper(auth()->user()->name ?? 'SISTEMA') }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer-40"></div>
    <h1 class="main-title">ACTA DE TRASLADO</h1>
    <div class="spacer-25"></div>

    <div class="content">
        Por medio del presente documento, el frente <strong>{{ strtoupper($frenteOrigen->NOMBRE_FRENTE ?? 'ORIGEN DESCONOCIDO') }}</strong> de la <strong>CONSTRUCTORA VIDALSA 27, C.A.</strong>, deja constancia formal del despacho y traslado de los equipos detallados a continuación hacia el frente <strong>{{ strtoupper($frenteDestino->NOMBRE_FRENTE ?? 'DESTINO DESCONOCIDO') }}</strong>, ubicado en <strong>{{ strtoupper($frenteDestino->UBICACION ?? 'UBICACIÓN NO ESPECIFICADA') }}</strong>.
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">N°</th>
                <th width="45%">DESCRIPCIÓN / EQUIPO</th>
                <th width="20%">MARCA</th>
                <th width="25%">SERIAL / PLACA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movilizaciones as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left; padding-left: 8px;">{{ strtoupper($item->equipo->tipo->nombre ?? 'N/A') }}</td>
                <td>{{ strtoupper($item->equipo->MARCA ?? 'N/A') }}</td>
                <td>{{ strtoupper($item->equipo->SERIAL_CHASIS ?? $item->equipo->documentacion->PLACA ?? '---') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures-container" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="50%" class="signature-item">
                <div class="firm-header">APROBADO POR (ORIGEN):</div>
                <div class="signature-line">
                    <span class="name">{{ strtoupper($frenteOrigen->RESP_1_NOM ?? 'SIN ASIGNAR') }}</span>
                    <span class="role">{{ strtoupper($frenteOrigen->RESP_1_CAR ?? 'RESPONSABLE') }}</span>
                </div>
            </td>
            <td width="50%" class="signature-item">
                @if(!empty($frenteOrigen->RESP_2_NOM))
                <div class="firm-header">APROBADO POR (ORIGEN):</div>
                <div class="signature-line">
                    <span class="name">{{ strtoupper($frenteOrigen->RESP_2_NOM) }}</span>
                    <span class="role">{{ strtoupper($frenteOrigen->RESP_2_CAR ?? 'RESPONSABLE') }}</span>
                </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="receptor-table-wrapper">
        <table border="0" cellpadding="0" cellspacing="0" align="center" style="width: 300px;">
            <tr>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; padding-bottom: 30px;">
                    RECIBIDO POR (DESTINO):
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid #000; padding-top: 15px;">
                    <table width="100%" border="0" cellpadding="2">
                        <tr>
                            <td width="25%" style="font-size: 9pt; font-weight: bold;">NOMBRE:</td>
                            <td width="75%" style="border-bottom: 0.5pt solid #000;"></td>
                        </tr>
                        <tr>
                            <td width="25%" style="font-size: 9pt; font-weight: bold;">C.I.:</td>
                            <td width="75%" style="border-bottom: 0.5pt solid #000;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
