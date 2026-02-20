<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body>

<!-- ===================== ENCABEZADO ===================== -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="30%" valign="top">
            @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" height="65" />
            @else
                <b><span style="font-size: 9pt;">VIDALSA 27</span></b>
            @endif
        </td>
        <td width="70%" align="right" valign="bottom" style="font-size: 8.5pt;">
            <b>FECHA DE EMISIÓN:</b> {{ date('d/m/Y') }}<br>
            <b>FRENTE DE ORIGEN:</b> {{ strtoupper($usuarioEmisor->frenteAsignado->NOMBRE_FRENTE ?? 'OFICINA PRINCIPAL') }}<br>
            EMITIDO POR SISTEMA DE GESTIÓN DE FLOTA
        </td>
    </tr>
</table>

<!-- Separador encabezado / N° Operación (12px) -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr><td height="12">&nbsp;</td></tr>
</table>

<!-- ===================== N° OPERACIÓN ===================== -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="right" style="font-size: 9pt;">
            <b>N° OPERACIÓN: {{ str_pad($movilizacion->CODIGO_CONTROL ?? 0, 6, '0', STR_PAD_LEFT) }}</b>
        </td>
    </tr>
</table>

<!-- Separador N° Operación / Título (20px) -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr><td height="20">&nbsp;</td></tr>
</table>

<!-- ===================== TÍTULO ===================== -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="font-size: 15pt;">
            <b>ACTA DE TRASLADO</b>
        </td>
    </tr>
</table>

<!-- Separador Título / Cuerpo (10px) -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr><td height="10">&nbsp;</td></tr>
</table>

<!-- ===================== CUERPO DEL TEXTO ===================== -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="justify" style="font-size: 10pt;">
            Por medio del presente documento, el frente <b>{{ strtoupper($usuarioEmisor->frenteAsignado->NOMBRE_FRENTE ?? 'OFICINA PRINCIPAL') }}</b> de la <b>CONSTRUCTORA VIDALSA 27, C.A.</b>, deja constancia formal del despacho y traslado de los equipos detallados a continuación hacia el frente <b>{{ strtoupper($frenteDestino->NOMBRE_FRENTE ?? 'DESTINO DESCONOCIDO') }}</b>, ubicado en <b>{{ strtoupper($frenteDestino->UBICACION ?? 'UBICACIÓN NO ESPECIFICADA') }}</b>.
        </td>
    </tr>
</table>

<!-- Separador Cuerpo / Tabla (14px) -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr><td height="14">&nbsp;</td></tr>
</table>

<!-- ===================== TABLA DE EQUIPOS =====================
     - Sin nobr en la tabla completa: permite que fluya entre páginas naturalmente
     - nobr="true" en cada fila: evita que UNA fila quede partida a mitad
     - thead: TCPDF repite el encabezado automáticamente en cada página nueva
-->
<table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
    <thead>
        <tr bgcolor="#e6f2ff">
            <th width="5%"  align="center" style="font-size: 8.5pt; font-weight: bold;">N°</th>
            <th width="50%" align="center" style="font-size: 8.5pt; font-weight: bold;">DESCRIPCIÓN / EQUIPO</th>
            <th width="20%" align="center" style="font-size: 8.5pt; font-weight: bold;">MARCA</th>
            <th width="25%" align="center" style="font-size: 8.5pt; font-weight: bold;">SERIAL / PLACA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($equipos as $index => $item)
        <tr nobr="true">
            <td width="5%"  align="center" style="font-size: 8.5pt;">{{ $index + 1 }}</td>
            <td width="50%" align="center" style="font-size: 8.5pt;">{{ strtoupper($item->tipo->nombre ?? 'SIN TIPO') }}</td>
            <td width="20%" align="center" style="font-size: 8.5pt;">{{ strtoupper($item->MARCA ?? '---') }}</td>
            <td width="25%" align="center" style="font-size: 8.5pt;">{{ strtoupper($item->SERIAL_CHASIS ?? '---') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- ===================== BLOQUE COMPLETO DE FIRMAS =====================
     nobr="true" en la tabla maestra: garantiza que las 3 firmas (Aprobado 1,
     Aprobado 2 y Recibido) NUNCA queden solas en otra hoja.
     Si no caben en la página actual, TCPDF las mueve completas a la siguiente.
-->
<table width="100%" border="0" cellpadding="0" cellspacing="0" nobr="true">

    <!-- Separador Tabla / Firmas (20px) dentro del bloque nobr -->
    <tr>
        <td colspan="3" height="20">&nbsp;</td>
    </tr>

    <!-- ── Fila: las dos firmas APROBADO POR (ORIGEN) ── -->
    <tr>
        <!-- Firma 1 -->
        <td width="45%" align="center" valign="bottom">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="font-size: 9pt;"><b>APROBADO POR (ORIGEN):</b></td>
                </tr>
                <tr>
                    <td height="45">&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <table width="85%" align="center" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="border-top: 0.5pt solid #000;"></td>
                            </tr>
                            <tr>
                                <td height="8">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size: 8.5pt;">
                                    {{ strtoupper($usuarioEmisor->frenteAsignado->RESP_1_NOM ?? 'SIN ASIGNAR') }}
                                </td>
                            </tr>
                            <tr>
                                <td height="4">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size: 8.5pt;">
                                    {{ strtoupper($usuarioEmisor->frenteAsignado->RESP_1_CAR ?? 'RESPONSABLE') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>

        <!-- Espacio central entre firmas -->
        <td width="10%"></td>

        <!-- Firma 2 -->
        <td width="45%" align="center" valign="bottom">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="font-size: 9pt;"><b>APROBADO POR (ORIGEN):</b></td>
                </tr>
                <tr>
                    <td height="45">&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <table width="85%" align="center" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="border-top: 0.5pt solid #000;"></td>
                            </tr>
                            <tr>
                                <td height="8">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size: 8.5pt;">
                                    {{ strtoupper($usuarioEmisor->frenteAsignado->RESP_2_NOM ?? 'SIN ASIGNAR') }}
                                </td>
                            </tr>
                            <tr>
                                <td height="4">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size: 8.5pt;">
                                    {{ strtoupper($usuarioEmisor->frenteAsignado->RESP_2_CAR ?? 'RESPONSABLE') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Separador entre Aprobado y Recibido (30px) -->
    <tr>
        <td colspan="3" height="30">&nbsp;</td>
    </tr>

    <!-- ── Fila: RECIBIDO POR (DESTINO) centrado con columnas vacías ── -->
    <tr>
        <td width="20%">&nbsp;</td>
        <td width="60%" align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="font-size: 9pt;"><b>RECIBIDO POR (DESTINO):</b></td>
                </tr>
                <tr>
                    <td height="45">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center">
                        <table width="80%" align="center" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="border-top: 0.5pt solid #000;"></td>
                            </tr>
                            <tr>
                                <td height="8">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size: 8.5pt;">Nombre: ___________________________</td>
                            </tr>
                            <tr>
                                <td height="4">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size: 8.5pt;">Cédula: ___________________________</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <td width="20%">&nbsp;</td>
    </tr>

</table>
<!-- Fin bloque nobr firmas -->

</body>
</html>
