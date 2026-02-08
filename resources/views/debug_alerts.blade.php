FECHA ACTUAL DEL SERVIDOR: {{ \Carbon\Carbon::now() }}
<br>
FECHA ACTUAL (startOfDay): {{ \Carbon\Carbon::now()->startOfDay() }}
<br>
FECHA +30 DÍAS: {{ \Carbon\Carbon::now()->addDays(30) }}

<hr>
<h2>DEBUGGER DE ALERTAS</h2>

@php
$now = \Carbon\Carbon::now()->startOfDay();
$in30Days = $now->copy()->addDays(30);

$equipos = \App\Models\Equipo::whereHas('documentacion', function($q) use ($in30Days) {
    $q->where('FECHA_VENC_POLIZA', '<', $in30Days)
      ->orWhere('FECHA_ROTC', '<', $in30Days)
      ->orWhere('FECHA_RACDA', '<', $in30Days);
})
->with(['documentacion', 'tipo'])
->limit(5)
->get();
@endphp

<table border="1" style="width:100%; border-collapse:collapse;">
<tr>
    <th>Equipo</th>
    <th>Campo</th>
    <th>Fecha BD</th>
    <th>Vencimiento</th>
    <th>Días hasta hoy</th>
    <th>Status</th>
</tr>
@foreach($equipos as $equipo)
    @php $doc = $equipo->documentacion; @endphp
    
    @if($doc->FECHA_VENC_POLIZA)
    <tr style="background: {{ \Carbon\Carbon::parse($doc->FECHA_VENC_POLIZA)->startOfDay()->lt($now) ? '#fee2e2' : '#fef3c7' }}">
        <td>{{ $equipo->MARCA }} {{ $equipo->MODELO }}</td>
        <td>POLIZA</td>
        <td>{{ $doc->FECHA_VENC_POLIZA }}</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_VENC_POLIZA)->format('Y-m-d') }}</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_VENC_POLIZA)->diffInDays($now, false) }} días</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_VENC_POLIZA)->startOfDay()->lt($now) ? 'VENCIDO' : 'POR VENCER' }}</td>
    </tr>
    @endif
    
    @if($doc->FECHA_ROTC)
    <tr style="background: {{ \Carbon\Carbon::parse($doc->FECHA_ROTC)->startOfDay()->lt($now) ? '#fee2e2' : '#fef3c7' }}">
        <td>{{ $equipo->MARCA }} {{ $equipo->MODELO }}</td>
        <td>ROTC</td>
        <td>{{ $doc->FECHA_ROTC }}</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_ROTC)->format('Y-m-d') }}</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_ROTC)->diffInDays($now, false) }} días</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_ROTC)->startOfDay()->lt($now) ? 'VENCIDO' : 'POR VENCER' }}</td>
    </tr>
    @endif
    
    @if($doc->FECHA_RACDA)
    <tr style="background: {{ \Carbon\Carbon::parse($doc->FECHA_RACDA)->startOfDay()->lt($now) ? '#fee2e2' : '#fef3c7' }}">
        <td>{{ $equipo->MARCA }} {{ $equipo->MODELO }}</td>
        <td>RACDA</td>
        <td>{{ $doc->FECHA_RACDA }}</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_RACDA)->format('Y-m-d') }}</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_RACDA)->diffInDays($now, false) }} días</td>
        <td>{{ \Carbon\Carbon::parse($doc->FECHA_RACDA)->startOfDay()->lt($now) ? 'VENCIDO' : 'POR VENCER' }}</td>
    </tr>
    @endif
@endforeach
</table>
