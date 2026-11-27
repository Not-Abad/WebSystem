<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\AlumnoTutor;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Collection;

final class TutorVinculacionService
{
    /** @return Collection<int, Tutor> */
    public function tutoresDe(Alumno $alumno): Collection
    {
        return $alumno->tutores()->orderBy('tutores.id_tutor')->get();
    }

    public function estaVinculado(Alumno $alumno, Tutor $tutor): bool
    {
        return $alumno->tutores()->where('tutores.id_tutor', $tutor->id_tutor)->exists();
    }

    public function vincular(Alumno $alumno, Tutor $tutor): void
    {
        if ($this->estaVinculado($alumno, $tutor)) {
            throw new \InvalidArgumentException('El alumno ya está vinculado a este tutor.');
        }

        if ($alumno->tutores()->count() >= AlumnoTutor::MAXIMO_TUTORES_POR_ALUMNO) {
            throw new \InvalidArgumentException('Un alumno solo puede tener 3 tutores como máximo.');
        }

        $alumno->tutores()->attach($tutor->id_tutor);

        // Se mantiene el vínculo principal histórico apuntando al último tutor
        // asignado, sin perder los demás vínculos de la tabla pivote.
        $alumno->update(['id_tutor' => $tutor->id_tutor]);
    }

    public function desvincular(Alumno $alumno, Tutor $tutor): void
    {
        if (! $this->estaVinculado($alumno, $tutor)) {
            throw new \InvalidArgumentException('El alumno no está vinculado a este tutor.');
        }

        $alumno->tutores()->detach($tutor->id_tutor);

        if ((int) $alumno->id_tutor === (int) $tutor->id_tutor) {
            $nuevoPrincipal = $alumno->tutores()->orderBy('tutores.id_tutor')->value('tutores.id_tutor');
            $alumno->update(['id_tutor' => $nuevoPrincipal]);
        }
    }
}
