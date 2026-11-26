@php
    $columnaActual = old('columna', $columnaBoletaSugerida);
    $periodoActual = old('periodo_escolar', $periodoBoleta);
@endphp
<div class="docente-opciones-reinco docente-opciones-boleta">
    <label>
        <span>Unidad de aprendizaje en la boleta</span>
        <select name="columna">
            @foreach ($columnasBoleta as $letra => $etiqueta)
                <option value="{{ $letra }}" @selected($columnaActual === $letra)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </label>
    <label>
        <span>Periodo escolar</span>
        <input type="text" name="periodo_escolar" maxlength="16"
               value="{{ $periodoActual }}" placeholder="26-27">
    </label>
    <label>
        <span>Nombre del director (opcional)</span>
        <input type="text" name="director" maxlength="120"
               value="{{ old('director', config('boleta.director')) }}" placeholder="Firma de la dirección">
    </label>
    <label>
        <span>Nombre del subdirector (opcional)</span>
        <input type="text" name="subdirector" maxlength="120"
               value="{{ old('subdirector', config('boleta.subdirector')) }}" placeholder="Firma de la subdirección">
    </label>
</div>
