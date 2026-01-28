@extends('layouts.estructura_base')

@section('title', 'Registrar Equipo')

@section('content')
<section class="page-title-card" style="margin: 0 auto 10px auto; text-align: center;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Registro de Equipo y Maquinaria</span>
    </h1>
</section>

<div class="admin-card" style="max-width: 1000px; margin: 0 auto;">
    <form id="createEquipoForm" action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Error Notification -->
        @if($errors->any())
            <div style="background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600;">
                <i class="material-icons" style="color: var(--maquinaria-red);">error_outline</i>
                Hay errores en el formulario. Por favor, revise los campos resaltados en rojo.
            </div>
        @endif

        @include('admin.equipos.partials.form_fields')

        <div style="margin-top: 40px; display: flex; gap: 12px; justify-content: center;">
            <a href="{{ route('equipos.index') }}" class="btn-primary-maquinaria" style="background-color: white; color: #0067b1; border: 1px solid #0067b1;">
                Cancelar
            </a>
            <button type="submit" class="btn-primary-maquinaria">
                <i class="material-icons">save</i>
                Registrar Equipo
            </button>
        </div>
    </form>
</div>

@endsection

@section('extra_js')
<script src="{{ asset('js/maquinaria/form_logic.js') }}?v=2.1"></script>
@endsection
