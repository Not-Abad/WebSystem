@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.alumnos.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a alumnos
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-plus"></i> Datos del nuevo alumno</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.alumnos.store') }}">
            @csrf
            @include('admin.alumnos._form')

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar alumno
                </button>
                <a href="{{ route('admin.alumnos.index') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
