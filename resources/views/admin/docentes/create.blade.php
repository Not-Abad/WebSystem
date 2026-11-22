@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.docentes.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a docentes
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-plus"></i> Datos del nuevo docente</h2>
                <p>Se creará una cuenta de acceso con el rol Docente.</p>
            </div>
        </div>

        @include('admin.docentes._form', [
            'action' => route('admin.docentes.store'),
        ])
    </section>
@endsection
