<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: helvetica; font-size: 10pt; color: #000; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { padding: 2px 0; }
        .data-table th {
            background-color: #e6f2ff; border: 0.5pt solid #000;
            font-weight: bold; text-align: center; padding: 5px; font-size: 8.5pt;
        }
        .data-table td {
            border: 0.5pt solid #000; padding: 4px 5px; font-size: 8.5pt;
        }
        .data-table tr { page-break-inside: avoid; }
        .section-title {
            font-size: 10pt; font-weight: bold; background-color: #f0f4f8;
            padding: 5px 8px; margin: 10px 0 5px 0; border-left: 3pt solid #0067b1;
        }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
        .badge-critica { background-color: #fef2f2; color: #dc2626; }
        .badge-alta { background-color: #fff7ed; color: #ea580c; }
        .badge-media { background-color: #fefce8; color: #ca8a04; }
        .badge-baja { background-color: #f0fdf4; color: #16a34a; }
        .badge-abierta { background-color: #fef2f2; color: #dc2626; }
        .badge-resuelta { background-color: #f0fdf4; color: #16a34a; }
        .badge-en_proceso { background-color: #fff7ed; color: #ea580c; }
    </style>
</head>
<body>

    <!-- Company Header -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="left" style="font-size: 8pt; color: #666;">CONSTRUCTORA VIDALSA 27, C.A.</td>
            <td align="right" style="font-size: 8pt; color: #666;">Fecha de emisión: {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr><td height="10">&nbsp;</td></tr>
    </table>

    <!-- Title -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-size: 15pt; font-weight: bold;">
                {{ $titulo ?? 'REPORTE DE FALLAS' }}
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr><td height="6">&nbsp;</td></tr>
    </table>

    <!-- Report Info -->
    <table width="100%" border="0" cellpadding="3" cellspacing="0" class="header-table">
        <tr>
            <td width="50%" style="font-size: 9.5pt;">
                <b>Frente de Trabajo:</b> {{ strtoupper($frente ?? 'N/A') }}
            </td>
            <td width="50%" align="right" style="font-size: 9.5pt;">
                <b>Fecha del Reporte:</b> {{ $fecha ?? date('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 9.5pt;">
                <b>Total de Fallas:</b> {{ $fallas->count() }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <b>Abiertas:</b> {{ $fallas->where('ESTADO_FALLA', 'ABIERTA')->count() }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <b>Resueltas:</b> {{ $fallas->where('ESTADO_FALLA', 'RESUELTA')->count() }}
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr><td height="10">&nbsp;</td></tr>
    </table>

    <!-- Faults Table -->
    <table width="100%" border="1" cellpadding="4" cellspacing="0" class="data-table">
        <thead>
            <tr bgcolor="#e6f2ff">
                <th width="4%" align="center">N°</th>
                <th width="14%">EQUIPO</th>
                <th width="10%">TIPO</th>
                <th width="8%">PRIORIDAD</th>
                <th width="30%">DESCRIPCIÓN DE FALLA</th>
                <th width="10%">SISTEMA</th>
                <th width="8%">ESTADO</th>
                <th width="8%">HORA</th>
                <th width="8%">REGISTRÓ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fallas as $index => $f)
            <tr nobr="true">
                <td align="center">{{ $index + 1 }}</td>
                <td>
                    <b>{{ strtoupper($f->equipo->tipo->nombre ?? 'S/T') }}</b><br>
                    <span style="font-size: 7.5pt;">{{ $f->equipo->MARCA ?? '' }} {{ $f->equipo->MODELO ?? '' }}</span><br>
                    <span style="font-size: 7pt; color: #666;">{{ $f->equipo->SERIAL_CHASIS ?? $f->equipo->CODIGO_PATIO ?? '' }}</span>
                </td>
                <td align="center">{{ $f->TIPO_FALLA }}</td>
                <td align="center">
                    <span class="badge badge-{{ strtolower($f->PRIORIDAD) }}">{{ $f->PRIORIDAD }}</span>
                </td>
                <td>{{ $f->DESCRIPCION_FALLA }}</td>
                <td align="center">{{ $f->SISTEMA_AFECTADO ?? '—' }}</td>
                <td align="center">
                    <span class="badge badge-{{ strtolower(str_replace(' ', '_', $f->ESTADO_FALLA)) }}">{{ str_replace('_', ' ', $f->ESTADO_FALLA) }}</span>
                </td>
                <td align="center">{{ $f->HORA_REGISTRO ? $f->HORA_REGISTRO->format('H:i') : '—' }}</td>
                <td style="font-size: 7.5pt;">{{ $f->usuarioRegistra->NOMBRE_COMPLETO ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Materials Section (if any fault has materials) -->
    @php $fallasConMateriales = $fallas->filter(fn($f) => $f->materiales && $f->materiales->count() > 0); @endphp

    @if($fallasConMateriales->count() > 0)
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr><td height="14">&nbsp;</td></tr>
    </table>

    <div class="section-title">MATERIALES REQUERIDOS / RECOMENDADOS</div>

    <table width="100%" border="1" cellpadding="4" cellspacing="0" class="data-table">
        <thead>
            <tr bgcolor="#e6f2ff">
                <th width="20%">EQUIPO</th>
                <th width="35%">MATERIAL</th>
                <th width="15%">ESPECIFICACIÓN</th>
                <th width="10%">CANTIDAD</th>
                <th width="10%">UNIDAD</th>
                <th width="10%">FUENTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fallasConMateriales as $f)
                @foreach($f->materiales as $mat)
                <tr nobr="true">
                    <td style="font-size: 7.5pt;">{{ $f->equipo->MARCA ?? '' }} {{ $f->equipo->MODELO ?? '' }}</td>
                    <td>{{ $mat->DESCRIPCION_MATERIAL }}</td>
                    <td align="center">{{ $mat->ESPECIFICACION ?? '—' }}</td>
                    <td align="center">{{ number_format($mat->CANTIDAD, 2) }}</td>
                    <td align="center">{{ $mat->UNIDAD }}</td>
                    <td align="center" style="font-size: 7.5pt;">{{ $mat->FUENTE === 'AUTO_CATALOGO' ? 'CATÁLOGO' : 'MANUAL' }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Signature Block -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" nobr="true">
        <tr><td height="30">&nbsp;</td></tr>
        <tr>
            <td width="45%" align="center" valign="bottom">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr><td align="center" style="font-size: 9pt;"><b>ELABORADO POR:</b></td></tr>
                    <tr><td height="35">&nbsp;</td></tr>
                    <tr>
                        <td>
                            <table width="85%" align="center" border="0" cellpadding="0" cellspacing="0">
                                <tr><td style="border-top: 0.5pt solid #000; height: 1px;"></td></tr>
                                <tr><td align="center" style="font-size: 8.5pt; line-height: 1.5;">Nombre: ___________________________</td></tr>
                                <tr><td height="1">&nbsp;</td></tr>
                                <tr><td align="center" style="font-size: 8.5pt; line-height: 1.5;">Cédula: ___________________________</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="10%"></td>
            <td width="45%" align="center" valign="bottom">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr><td align="center" style="font-size: 9pt;"><b>REVISADO POR:</b></td></tr>
                    <tr><td height="35">&nbsp;</td></tr>
                    <tr>
                        <td>
                            <table width="85%" align="center" border="0" cellpadding="0" cellspacing="0">
                                <tr><td style="border-top: 0.5pt solid #000; height: 1px;"></td></tr>
                                <tr><td align="center" style="font-size: 8.5pt; line-height: 1.5;">Nombre: ___________________________</td></tr>
                                <tr><td height="1">&nbsp;</td></tr>
                                <tr><td align="center" style="font-size: 8.5pt; line-height: 1.5;">Cédula: ___________________________</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
