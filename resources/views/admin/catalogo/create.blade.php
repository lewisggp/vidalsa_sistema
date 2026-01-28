@extends('layouts.estructura_base')

@section('title', 'Nuevo Modelo - Catálogo')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <section class="page-title-card" style="margin: 0 auto 10px auto; text-align: center;">
        <h1 class="page-title">
            <span class="page-title-line2" style="color: #000;">Registro de Modelo</span>
        </h1>
    </section>

    <div class="admin-card">
        <form id="catalogoForm" action="{{ route('catalogo.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @include('admin.catalogo.partials.form_fields')

            <div style="margin-top: 40px; display: flex; gap: 12px; justify-content: center;">
                <a href="{{ route('catalogo.index') }}" class="btn-primary-maquinaria" style="background-color: white; color: #0067b1; border: 1px solid #0067b1;">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary-maquinaria">
                    <i class="material-icons">save</i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('extra_js')
    <script>
        // Preview Logic for Image (Inline for simplicity or move to JS file)
        document.getElementById('foto_referencial').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview_referencial');
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;">`;
                    preview.style.borderColor = 'var(--maquinaria-blue)';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
@endsection
