@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.docentes.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a docentes
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-pen"></i> Editar docente</h2>
            </div>
        </div>

        @include('admin.docentes._form', [
            'action' => route('admin.docentes.update', $docente),
        ])
    </section>
@endsection
