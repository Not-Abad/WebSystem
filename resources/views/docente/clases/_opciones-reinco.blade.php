@php
    $estatusActual = old('estatus', $evaluacion->estatus ?? config('reinco.estatus'));
    $formatoActual = old('formato_calificacion', $evaluacion->formato_calificacion ?? config('reinco.formato_calificacion'));
@endphp
<div class="docente-opciones-reinco">
    <label>
        <span>Estatus</span>
        <select name="estatus">
            <option value="ordinaria" @selected($estatusActual === 'ordinaria')>Ordinaria</option>
            <option value="final" @selected($estatusActual === 'final')>Final</option>
            <option value="R1" @selected($estatusActual === 'R1')>Regularización R1</option>
            <option value="R2" @selected($estatusActual === 'R2')>Regularización R2</option>
            <option value="R3" @selected($estatusActual === 'R3')>Regularización R3</option>
        </select>
    </label>
    <label>
        <span>Calificación</span>
        <select name="formato_calificacion">
            <option value="decimal" @selected($formatoActual === 'decimal')>Decimal (0.00)</option>
            <option value="entero" @selected($formatoActual === 'entero')>Entero (0)</option>
        </select>
    </label>
</div>
