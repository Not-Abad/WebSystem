<div class="form-campo {{ $clase ?? '' }}">
    <label for="{{ $id }}">{{ $etiqueta }}</label>
    <div class="ruta-campo">
        <input type="text" id="{{ $id }}" name="{{ $name }}"
               value="{{ $valor }}" maxlength="255"
               @if ($requerido ?? false) required @endif
               placeholder="{{ $placeholder ?? '' }}">
        <button type="button" class="btn-archivo" onclick="abrirGestorArchivos('{{ $id }}', this)"
                title="Abrir gestor de archivos" aria-label="Abrir gestor de archivos">
            <i class="fas fa-folder-open"></i>
        </button>
    </div>
    <small>{{ $ayuda ?? 'Usa el botón de la carpeta para buscar el archivo en el gestor.' }}</small>
</div>
