<div class="criterio-opcion {{ $criterio ? 'seleccionado' : '' }}" data-criterio-opcion>
    <label class="criterio-seleccion">
        <input type="checkbox" class="criterio-check"
               name="criterios[{{ $indice }}][seleccionado]" value="1"
               {{ old('criterios.'.$indice.'.seleccionado', $criterio ? '1' : null) ? 'checked' : '' }}>
        <span>{{ $evidencia }}</span>
    </label>
    <input type="hidden" name="criterios[{{ $indice }}][tipo_evidencia]" value="{{ $evidencia }}">
    <input type="hidden" name="criterios[{{ $indice }}][nombre_rubro]" value="{{ $evidencia }}">
    <div class="criterio-campos">
        <label>
            Porcentaje
            <input type="number" class="criterio-porcentaje"
                   name="criterios[{{ $indice }}][porcentaje]"
                   value="{{ old('criterios.'.$indice.'.porcentaje', $criterio?->porcentaje) }}"
                   min="1" step="0.01" placeholder="%" {{ $criterio ? '' : 'disabled' }}>
        </label>
        <label>
            Fecha de entrega
            <input type="date" class="criterio-fecha"
                   name="criterios[{{ $indice }}][fecha_entrega]"
                   value="{{ old('criterios.'.$indice.'.fecha_entrega', $criterio?->fecha_entrega?->format('Y-m-d')) }}"
                   min="2000-01-01" max="2100-12-31" {{ $criterio ? '' : 'disabled' }}>
        </label>
    </div>
</div>
