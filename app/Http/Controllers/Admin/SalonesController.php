<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SalonesController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $salones = Salon::when($busqueda !== '', function ($query) use ($like) {
            $query->where(function ($query) use ($like) {
                $query->where('clave', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('edificio', 'like', $like);
            });
        })
            ->when($request->filled('estatus'), function ($query) use ($request) {
                $query->where('is_active', (bool) $request->input('estatus'));
            })
            ->orderBy('clave')
            ->paginate(20)
            ->withQueryString();

        return view('admin.salones.index', [
            'titulo' => 'Salones',
            'subtitulo' => 'Catálogo de espacios disponibles para asignar clases en el horario.',
            'icono' => 'fa-door-open',
            'etiqueta' => 'Administrador',
            'salones' => $salones,
            'busqueda' => $busqueda,
            'filtros' => $request->only(['q', 'estatus']),
        ]);
    }

    public function create()
    {
        return view('admin.salones.form', [
            'titulo' => 'Nuevo salón',
            'subtitulo' => 'Registra un espacio con su clave, capacidad y ubicación.',
            'icono' => 'fa-plus',
            'etiqueta' => 'Salones',
            'salon' => null,
        ]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->reglas(), $this->mensajes())->validate();

        $salon = Salon::create([
            'clave' => strtoupper(trim((string) $request->input('clave'))),
            'nombre' => trim((string) $request->input('nombre')),
            'capacidad' => (int) $request->input('capacidad'),
            'edificio' => trim((string) $request->input('edificio')) ?: null,
            'is_active' => true,
        ]);

        BitacoraService::registrar(
            'salones',
            'ALTA',
            "Alta de salón: {$salon->clave} · {$salon->nombre}.",
            $salon->id_salon
        );

        return redirect()->route('admin.salones.index')
            ->with('status', 'Salón creado correctamente.');
    }

    public function edit(Salon $salon)
    {
        return view('admin.salones.form', [
            'titulo' => 'Editar salón',
            'subtitulo' => "Actualización del salón {$salon->clave}.",
            'icono' => 'fa-pen',
            'etiqueta' => 'Salones',
            'salon' => $salon,
        ]);
    }

    public function update(Request $request, Salon $salon)
    {
        Validator::make($request->all(), $this->reglas($salon), $this->mensajes())->validate();

        $salon->update([
            'clave' => strtoupper(trim((string) $request->input('clave'))),
            'nombre' => trim((string) $request->input('nombre')),
            'capacidad' => (int) $request->input('capacidad'),
            'edificio' => trim((string) $request->input('edificio')) ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        BitacoraService::registrar(
            'salones',
            'EDICION',
            "Edición de salón: {$salon->clave} · {$salon->nombre}.",
            $salon->id_salon
        );

        return redirect()->route('admin.salones.index')
            ->with('status', 'Salón actualizado correctamente.');
    }

    public function destroy(Salon $salon)
    {
        $salon->update(['is_active' => false]);

        BitacoraService::registrar(
            'salones',
            'BAJA',
            "Baja lógica de salón: {$salon->clave} · {$salon->nombre}.",
            $salon->id_salon
        );

        return redirect()->route('admin.salones.index')
            ->with('status', 'Salón dado de baja lógica. Puedes reactivarlo desde Editar.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglas(?Salon $salon = null): array
    {
        $clave = Rule::unique('salones', 'clave');
        if ($salon) {
            $clave->ignore($salon->id_salon, 'id_salon');
        }

        return [
            'clave' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/', $clave],
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:500'],
            'edificio' => ['nullable', 'string', 'max:60'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mensajes(): array
    {
        return [
            'clave.required' => 'Ingresa la clave del salón.',
            'clave.regex' => 'La clave solo puede contener letras, números y guiones.',
            'clave.unique' => 'Esa clave ya está registrada.',
            'nombre.required' => 'Ingresa el nombre del salón.',
            'capacidad.required' => 'Ingresa la capacidad del salón.',
            'capacidad.min' => 'La capacidad debe ser al menos 1.',
            'capacidad.max' => 'La capacidad no puede superar 500.',
        ];
    }
}
