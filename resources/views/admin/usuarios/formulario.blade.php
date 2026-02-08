@extends('layouts.estructura_base')

@section('title', isset($user) ? 'Editar Usuario' : 'Nuevo Usuario')

@section('content')
<section class="page-title-card" style="text-align: center; margin: 0 auto 10px auto;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">{{ isset($user) ? 'Edición de Usuario' : 'Registro de Usuario' }}</span>
    </h1>
</section>

<div class="admin-card" style="max-width: 800px; margin: 0 auto;">
    <form id="userForm" action="{{ isset($user) ? route('usuarios.update', $user->ID_USUARIO) : route('usuarios.store') }}" method="POST">
        @csrf
        @if(isset($user))
            @method('PUT')
        @endif

        <div class="form-grid">
            <div>
                <label for="NOMBRE_COMPLETO" class="form-label">Nombre Completo</label>
                <input type="text" id="NOMBRE_COMPLETO" name="NOMBRE_COMPLETO" class="form-input-custom @error('NOMBRE_COMPLETO') is-invalid @enderror" value="{{ old('NOMBRE_COMPLETO', $user->NOMBRE_COMPLETO ?? '') }}" required autocomplete="off">
                @error('NOMBRE_COMPLETO')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="CORREO_ELECTRONICO" class="form-label">Email Corporativo</label>
                <input type="email" id="CORREO_ELECTRONICO" name="CORREO_ELECTRONICO" class="form-input-custom @error('CORREO_ELECTRONICO') is-invalid @enderror" value="{{ old('CORREO_ELECTRONICO', $user->CORREO_ELECTRONICO ?? '') }}" required autocomplete="off" style="text-transform: lowercase;">
                @error('CORREO_ELECTRONICO')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <span id="lbl_usuario_estatus_title" class="form-label">Estatus de Cuenta</span>
                <div class="custom-dropdown" id="statusSelect">
                    <input type="hidden" name="ESTATUS" id="input_estatus" value="{{ old('ESTATUS', $user->ESTATUS ?? 'ACTIVO') }}" aria-label="Estatus de Cuenta">
                    <div class="dropdown-trigger" id="trigger_estatus" onclick="toggleDropdown('statusSelect', event)" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_usuario_estatus_title label_estatus" style="cursor: default;">
                        <span id="label_estatus">{{ old('ESTATUS', $user->ESTATUS ?? 'ACTIVO') }}</span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="dropdown-content">
                        <div class="dropdown-item {{ old('ESTATUS', $user->ESTATUS ?? 'ACTIVO') == 'ACTIVO' ? 'selected' : '' }}" onclick="selectOption('statusSelect', 'ACTIVO', 'ACTIVO', 'estatus')">ACTIVO</div>
                        <div class="dropdown-item {{ old('ESTATUS', $user->ESTATUS ?? 'ACTIVO') == 'INACTIVO' ? 'selected' : '' }}" onclick="selectOption('statusSelect', 'INACTIVO', 'INACTIVO', 'estatus')">INACTIVO</div>
                    </div>
                </div>
                @error('ESTATUS')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
            </div>


            <div>
                <label for="password" class="form-label">Clave de Acceso</label>
                <input type="password" id="password" name="password" class="form-input-custom @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }} placeholder="{{ isset($user) ? 'Dejar en blanco para mantener la actual' : '' }}" autocomplete="new-password">
                @error('password')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
                @if(isset($user))
                    <small style="color: var(--maquinaria-gray-text); font-size: 12px; display: block; margin-top: 5px;">
                        Dejar vacío si no desea cambiar la contraseña
                    </small>
                @endif
            </div>

            <div>
                <span id="lbl_usuario_rol_title" class="form-label">Rol Asignado</span>
                <div class="custom-dropdown" id="roleSelect">
                    <input type="hidden" name="ID_ROL" id="input_rol" value="{{ old('ID_ROL', $user->ID_ROL ?? '') }}" aria-label="Rol Asignado">
                    <div class="dropdown-trigger" id="trigger_rol" onclick="toggleDropdown('roleSelect', event)" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_usuario_rol_title label_rol" style="cursor: default;">
                        <span id="label_rol">
                            @php 
                                $currentRol = $roles->firstWhere('ID_ROL', old('ID_ROL', $user->ID_ROL ?? ''));
                            @endphp
                            {{ $currentRol ? $currentRol->NOMBRE_ROL : 'Seleccione un rol...' }}
                        </span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="dropdown-content">
                        @foreach($roles as $rol)
                            <div class="dropdown-item {{ old('ID_ROL', $user->ID_ROL ?? '') == $rol->ID_ROL ? 'selected' : '' }}" onclick="selectOption('roleSelect', '{{ $rol->ID_ROL }}', '{{ $rol->NOMBRE_ROL }}', 'rol')">
                                {{ $rol->NOMBRE_ROL }}
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('ID_ROL')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <span id="lbl_usuario_nivel_title" class="form-label">Nivel de Acceso</span>
                <div class="custom-dropdown" id="levelSelect">
                    <input type="hidden" name="NIVEL_ACCESO" id="input_nivel" value="{{ old('NIVEL_ACCESO', $user->NIVEL_ACCESO ?? '') }}" aria-label="Nivel de Acceso">
                    <div class="dropdown-trigger" id="trigger_nivel" onclick="toggleDropdown('levelSelect', event)" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_usuario_nivel_title label_nivel" style="cursor: default;">
                        <span id="label_nivel">
                            @if(old('NIVEL_ACCESO', $user->NIVEL_ACCESO ?? '') == 1)
                                GLOBAL - ACCESO COMPLETO
                            @elseif(old('NIVEL_ACCESO', $user->NIVEL_ACCESO ?? '') == 2)
                                LOCAL - LIMITADO A UN FRENTE
                            @else
                                Seleccione nivel de acceso...
                            @endif
                        </span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="dropdown-content">
                        <div class="dropdown-item {{ old('NIVEL_ACCESO', $user->NIVEL_ACCESO ?? '') == 1 ? 'selected' : '' }}" onclick="selectOption('levelSelect', '1', 'GLOBAL - ACCESO COMPLETO', 'nivel')">
                            GLOBAL - ACCESO COMPLETO
                        </div>
                        <div class="dropdown-item {{ old('NIVEL_ACCESO', $user->NIVEL_ACCESO ?? '') == 2 ? 'selected' : '' }}" onclick="selectOption('levelSelect', '2', 'LOCAL - LIMITADO A UN FRENTE', 'nivel')">
                            LOCAL - LIMITADO A UN FRENTE
                        </div>
                    </div>
                </div>
                @error('NIVEL_ACCESO')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <span id="lbl_usuario_frente_title" class="form-label">Frente Asignado</span>
                <div class="custom-dropdown" id="frenteSelect">
                    <input type="hidden" name="ID_FRENTE_ASIGNADO" id="input_frente" value="{{ old('ID_FRENTE_ASIGNADO', $user->ID_FRENTE_ASIGNADO ?? '') }}" aria-label="Frente Asignado">
                    <div class="dropdown-trigger" id="trigger_frente" onclick="toggleDropdown('frenteSelect', event)" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_usuario_frente_title label_frente" style="cursor: default;">
                        <span id="label_frente">
                            @php 
                                $currentFrente = $frentes->firstWhere('ID_FRENTE', old('ID_FRENTE_ASIGNADO', $user->ID_FRENTE_ASIGNADO ?? ''));
                            @endphp
                            {{ $currentFrente ? $currentFrente->NOMBRE_FRENTE : 'Seleccione frente de trabajo...' }}
                        </span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="dropdown-content">
                        @foreach($frentes as $frente)
                            <div class="dropdown-item {{ old('ID_FRENTE_ASIGNADO', $user->ID_FRENTE_ASIGNADO ?? '') == $frente->ID_FRENTE ? 'selected' : '' }}" onclick="selectOption('frenteSelect', '{{ $frente->ID_FRENTE }}', '{{ $frente->NOMBRE_FRENTE }}', 'frente')">
                                {{ $frente->NOMBRE_FRENTE }}
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('ID_FRENTE_ASIGNADO')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
                <small style="color: var(--maquinaria-gray-text); font-size: 12px; display: block; margin-top: 5px;">
                    Este campo es obligatorio para la asignación de recursos
                </small>
            </div>

            <div>
                <span id="lbl_permisos_title" class="form-label">Permisos de Sección</span>
                
                <div class="custom-multiselect" id="permissionsSelect">
                    <div class="multiselect-trigger" id="multiselectTrigger" onclick="toggleDropdown('permissionsSelect', event)" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_permisos_title selectedCount" style="cursor: default;">
                        <span id="selectedCount">Seleccione permisos...</span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="multiselect-content" id="multiselectContent">
                        @php $user_perms = old('PERMISOS', $user->PERMISOS ?? []); @endphp
                        @foreach($available_permissions as $index => $perm)
                            <label class="multiselect-item" for="perm_{{ $index }}">
                                <input type="checkbox" id="perm_{{ $index }}" name="PERMISOS[]" value="{{ $perm }}" {{ in_array($perm, $user_perms) ? 'checked' : '' }} onchange="updateSelectedCount()">
                                <span>{{ $perm }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('PERMISOS')
                    <span class="error-message-inline">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 12px; justify-content: center;">
            <a href="{{ route('usuarios.index') }}" class="btn-primary-maquinaria btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary-maquinaria">
                <i class="material-icons">save</i>
                {{ isset($user) ? 'Actualizar Información' : 'Registrar en el Sistema' }}
            </button>
        </div>
    </form>
</div>

@endsection

@section('extra_js')
<script src="{{ asset('js/maquinaria/form_logic.js') }}?v=2.1"></script>
@endsection
