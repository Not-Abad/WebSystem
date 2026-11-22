<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    private function docenteAutenticado(): Docente
    {
        return Auth::user()->docente()->firstOrFail();
    }

    public function index()
    {
        $docente = $this->docenteAutenticado()->load(['capacitacion', 'area', 'usuario']);

        return view('docente.perfil.index', [
            'titulo' => 'Mis datos',
            'subtitulo' => 'Consulta tu ficha institucional y actualiza tu imagen de perfil.',
            'icono' => 'fa-id-card',
            'etiqueta' => 'Docente',
            'docente' => $docente,
            'iniciales' => $this->iniciales($docente->nombreCompleto()),
        ]);
    }

    public function actualizarFoto(Request $request)
    {
        $docente = $this->docenteAutenticado();

        $datos = $request->validate([
            'foto_perfil' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],
        ], [
            'foto_perfil.required' => 'Selecciona una imagen de perfil.',
            'foto_perfil.image' => 'El archivo debe ser una imagen válida.',
            'foto_perfil.mimes' => 'Usa una imagen JPG, PNG o WEBP.',
            'foto_perfil.max' => 'La imagen no debe superar los 2 MB.',
        ]);

        $rutaAnterior = $docente->foto_perfil_ruta;
        $ruta = $request->file('foto_perfil')->store('perfiles/docentes', 'public');

        $docente->update(['foto_perfil_ruta' => $ruta]);

        if ($rutaAnterior && $rutaAnterior !== $ruta && Storage::disk('public')->exists($rutaAnterior)) {
            Storage::disk('public')->delete($rutaAnterior);
        }

        BitacoraService::registrar(
            'docentes',
            'EDICION',
            'Actualización de la imagen de perfil del docente.',
            $docente->id_docente
        );

        return back()->with('status', 'Tu imagen de perfil se actualizó correctamente.');
    }

    /**
     * @return string
     */
    private function iniciales(string $nombre): string
    {
        return collect(preg_split('/\s+/', trim($nombre)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($palabra) => mb_strtoupper(mb_substr($palabra, 0, 1)))
            ->implode('');
    }
}
