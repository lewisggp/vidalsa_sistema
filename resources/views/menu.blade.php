@extends('layouts.estructura_base')

@section('title', 'Tablero de Control')

@section('content')
<!-- Styles moved to top to prevent FOUC -->


<div class="dashboard-container" style="padding: 10px 20px;">
    
    <!-- Header -->
    <section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
        <h1 class="page-title">
            <span class="page-title-line2" style="color: #000;">Sistema de Gestión de Equipos Operacionales</span>
        </h1>
    </section>

    <!-- Main Grid -->
    <div class="dashboard-grid">
        
        <!-- Column 1: Resumen Rápido (Cards) -->
        <div class="card-section" style="grid-column: span 12;">
            <div class="cards-wrapper">
                

                <!-- Card 3: Movilizaciones -->
                <div class="dashboard-card card-purple">
                    <div class="icon-wrapper">
                        <i class="material-icons">today</i>
                    </div>
                    <div class="card-content">
                        <span class="card-label">Movilizaciones Hoy</span>
                        <div class="card-value-row">
                            <span class="card-value">{{ $movilizacionesHoy }}</span>
                        </div>
                        <span class="card-subtext">{{ $pendientes }} Por Confirmar</span>
                    </div>
                </div>

                <!-- Card 4: Alertas -->
                <div class="dashboard-card card-red">
                    <div class="icon-wrapper">
                        <i class="material-icons">warning</i>
                    </div>
                    <div class="card-content">
                        <span class="card-label">Alertas Documentos</span>
                        <div class="card-value-row">
                            <span class="card-value">{{ $totalAlerts }}</span>
                        </div>
                        <span class="card-subtext">Documentos Vencidos</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Row 2 -->
        <div class="grid-row-2">
            

            <!-- Última Actividad -->
            <div class="content-card activity-card">
                <h3 class="card-title">Movilizaciones Pendientes</h3>
                <div class="activity-list">
                    @forelse($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="material-icons">local_shipping</i>
                        </div>
                        <div class="activity-info">
                            <p class="activity-text">
                                <strong>{{ $activity->equipo->SERIAL_CHASIS ?? 'Equipo' }}</strong> → {{ $activity->frenteDestino->NOMBRE_FRENTE ?? 'Destino' }}
                            </p>
                            <span class="activity-time">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="material-icons">inbox</i>
                        <p>No hay movilizaciones por confirmar.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Documentos Vencidos (Polizas, ROTC, RACDA) -->
            <div class="content-card policies-card">
                <h3 class="card-title" style="color: #ef4444;">Documentos Vencidos</h3>
                <div class="activity-list">
                    @forelse($expiredList as $alert)
                    <div class="activity-item" style="cursor: default;" onclick="openPdfPreview('{{ $alert->current_link }}', '{{ $alert->type_key }}', '{{ $alert->label }}', {{ $alert->equipo->ID_EQUIPO }})">
                        <div class="activity-icon" style="background: #fee2e2; color: #991b1b;">
                            <i class="material-icons">warning</i>
                        </div>
                        <div class="activity-info">
                            <p class="activity-text">
                                <strong>{{ $alert->equipo->tipo->nombre ?? 'Equipo' }} {{ $alert->equipo->MARCA }} {{ $alert->equipo->MODELO }}</strong>
                                <br>
                                <span style="font-size: 0.9em; font-weight: normal; color: #64748b;">
                                    {{ $alert->equipo->SERIAL_CHASIS ?? $alert->equipo->PLACA ?? '' }}
                                </span>
                            </p>
                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                                <span class="activity-time" style="color: #ef4444; font-weight: 600;">
                                    {{ $alert->label }}: {{ ucfirst(\Carbon\Carbon::parse($alert->fecha)->locale('es')->diffForHumans()) }}
                                </span>
                                <i class="material-icons" style="font-size: 16px; color: #94a3b8;">edit</i>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="material-icons" style="color: #cbd5e0;">check_circle</i>
                        <p>Todos los documentos están vigentes.</p>
                    </div>
                    @endforelse
                </div>
            </div>



        </div>

    </div>
</div>





@endsection
