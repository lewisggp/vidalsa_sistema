@extends('layouts.estructura_base')

@section('title', 'Editar Equipo')

@section('content')
<section class="page-title-card">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Edición de Equipo</span>
    </h1>
</section>

<div class="admin-card" style="max-width: 1000px; margin: 0 auto;">
    <form id="editEquipoForm" action="{{ route('equipos.update', $equipo->ID_EQUIPO) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')


        @include('admin.equipos.partials.form_fields')

        <div style="margin-top: 40px; display: flex; gap: 12px; justify-content: center;">
            <a href="{{ route('equipos.index') }}" class="btn-primary-maquinaria" style="background-color: #edf2f7; color: #4a5568;">
                Cancelar
            </a>
            <button type="submit" class="btn-primary-maquinaria">
                <i class="material-icons">save</i>
                Actualizar Equipo
            </button>
        </div>
    </form>
</div>
@endsection
