@if($fallas->count())
<table class="mant-table">
    <thead>
        <tr>
            <th>Hora</th>
            <th>Equipo</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fallas as $f)
        <tr data-falla-id="{{ $f->ID_FALLA }}">
            <td style="font-weight:700; white-space:nowrap; font-size:12px;">
                {{ $f->HORA_REGISTRO ? $f->HORA_REGISTRO->format('H:i') : '—' }}
            </td>
            <td>
                <div style="font-weight:700; color:#1e293b; font-size:12px;">
                    {{ $f->equipo->tipo->nombre ?? 'S/T' }}
                </div>
                <div style="font-size:11px; color:#64748b;">
                    {{ $f->equipo->MARCA ?? '' }} {{ $f->equipo->MODELO ?? '' }}
                    <span style="color:#94a3b8;">| {{ $f->equipo->SERIAL_CHASIS ?? $f->equipo->CODIGO_PATIO ?? '' }}</span>
                </div>
            </td>
            <td style="font-size:12px;">{{ $f->TIPO_FALLA }}</td>
            <td style="max-width:250px;">
                <div style="font-size:12px; color:#334155; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                    {{ $f->DESCRIPCION_FALLA }}
                </div>
                @if($f->SISTEMA_AFECTADO)
                    <div style="font-size:10px; color:#94a3b8; margin-top:2px;">Sistema: {{ $f->SISTEMA_AFECTADO }}</div>
                @endif
            </td>
            <td>
                <span class="badge-prioridad {{ strtolower($f->PRIORIDAD) }}">
                    {{ $f->PRIORIDAD }}
                </span>
            </td>
            <td>
                <span class="badge-estado {{ strtolower(str_replace(' ', '_', $f->ESTADO_FALLA)) }}">
                    {{ str_replace('_', ' ', $f->ESTADO_FALLA) }}
                </span>
            </td>
            <td style="white-space:nowrap;">
                @if($f->ESTADO_FALLA === 'ABIERTA')
                    <button class="btn-mant-sm btn-mant-success" onclick="resolverFalla({{ $f->ID_FALLA }})" title="Marcar como resuelta">
                        <i class="material-icons" style="font-size:14px;">check</i>
                    </button>
                @endif
                <button class="btn-mant-sm btn-mant-info" onclick="verDetalleFalla({{ $f->ID_FALLA }})" title="Ver detalle">
                    <i class="material-icons" style="font-size:14px;">info</i>
                </button>
                <a href="{{ route('mantenimiento.falla.pdf', $f->ID_FALLA) }}" class="btn-mant-sm btn-mant-info" data-no-spa="true" title="PDF" style="text-decoration:none;">
                    <i class="material-icons" style="font-size:14px;">picture_as_pdf</i>
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="mant-empty">
    <i class="material-icons">check_circle</i>
    <p>No hay fallas registradas en este reporte</p>
</div>
@endif
