<?php

namespace App\Services;

use App\Data\Reinco\ReincoExportData;
use App\Models\Clase;
use App\Models\Evaluacion;

/**
 * Genera el concentrado REINCO usado por el plantel a partir de una entrada
 * estructurada (ReincoExportData), separando los datos de evaluación de la
 * presentación del Excel.
 *
 * Hojas: Datos generales, REINCO 205, REINCO 206-A, 206-A reverso y REINCO 206-B.
 */
class ReincoService
{
    private const FILAS_CABECERA = 8;

    /** @var array<int, string> */
    private const TITULO_HOJAS = [
        'DATOS GENERALES',
        'REINCO 205 · LISTA DE ASISTENCIA',
        'REINCO 206-A · EVIDENCIAS Y PORCENTAJES',
        'REINCO 206-A (REVERSO) · DEFINICIÓN DE EVIDENCIAS',
        'REINCO 206-B · REGISTRO DE EVALUACIÓN Y ASISTENCIA',
    ];

    /**
     * @param  array<string, mixed>  $opciones
     */
    public function generar(
        Clase $clase,
        Evaluacion $evaluacion,
        string $rutaAbsoluta,
        array $opciones = []
    ): int {
        $datos = ReincoExportData::desdeClase($clase, $evaluacion, $opciones, count(self::TITULO_HOJAS));

        $writer = new XlsxWriter();
        $writer->usarPlantilla($datos->presentacion->plantillaBase !== ''
            ? $datos->presentacion->plantillaBase
            : null);

        $writer->agregarHoja(
            'Datos generales',
            $this->hojaDatosGenerales($datos),
            $this->combinacionesCabecera(4),
            [26, 52, 24, 22],
            $this->opcionesHoja($datos)
        );
        $writer->agregarHoja(
            'REINCO 205',
            $this->hoja205($datos),
            $this->combinacionesCabecera(16),
            [7, 20, 34, 12, 10, 11, 12, 10, 11, 12, 10, 11, 13, 10, 11, 30],
            $this->opcionesHoja($datos)
        );
        $writer->agregarHoja(
            'REINCO 206-A',
            $this->hoja206A($datos),
            $this->combinacionesCabecera(3 + $datos->evidencias->count() + 3),
            array_merge([7, 20, 34], array_fill(0, $datos->evidencias->count(), 24), [13, 15, 16]),
            $this->opcionesHoja($datos)
        );
        $writer->agregarHoja(
            '206-A reverso',
            $this->reverso206A($datos),
            $this->combinacionesCabecera(6),
            [8, 30, 22, 12, 16, 16],
            $this->opcionesHoja($datos)
        );
        $writer->agregarHoja(
            'REINCO 206-B',
            $this->hoja206B($datos),
            $this->combinacionesCabecera(6 + $datos->evidencias->count() + 5),
            array_merge([7, 20, 34, 13, 10, 12], array_fill(0, $datos->evidencias->count(), 18), [16, 16, 12, 14]),
            $this->opcionesHoja($datos)
        );

        return $writer->guardar($rutaAbsoluta);
    }

    /** @return array<string, mixed> */
    private function opcionesHoja(ReincoExportData $datos): array
    {
        $presentacion = $datos->presentacion;

        return [
            'impresion' => [
                'orientacion' => $presentacion->orientacion,
                'papel' => $presentacion->papel,
                'ajustar_ancho' => $presentacion->ajustarAncho,
                'ajustar_alto' => $presentacion->ajustarAlto,
                'margenes' => $presentacion->margenes,
                'centrar_horizontal' => $presentacion->centrarHorizontal,
            ],
            'pie' => 'REINCO · CCT '.$datos->institucional['cct'].' · Hoja &P de &N',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function combinacionesCabecera(int $columnas): array
    {
        $ultima = $this->letraColumna($columnas);
        $combinaciones = [];
        foreach ([1, 2, 3, 4, 5, 7] as $fila) {
            $combinaciones[] = 'A'.$fila.':'.$ultima.$fila;
        }

        return $combinaciones;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function encabezado(ReincoExportData $datos, int $numeroHoja): array
    {
        $institucional = $datos->institucional;
        $grupo = $datos->grupo;
        $materia = $datos->materia;
        $evaluacion = $datos->evaluacion;

        return [
            [XlsxWriter::texto($institucional['plantel'], 2)],
            [XlsxWriter::texto('CCT: '.$institucional['cct'].' · Nivel: '.$institucional['nivel'].' · Turno: '.$institucional['turno'], 1)],
            [XlsxWriter::texto('Semestre: '.$grupo['semestre'].' · Grupo: '.$grupo['grupo'].' · Plan/retícula: '.$grupo['plan_retricula'].' · Área/componente: '.$grupo['area_componente'])],
            [XlsxWriter::texto('Materia: '.$materia['clave'].' · '.$materia['nombre'].' · Docente: '.$materia['docente'].' · Periodo escolar: '.$institucional['periodo_escolar'])],
            [XlsxWriter::texto(
                'Hoja '.$numeroHoja.' de '.$datos->presentacion->hojas
                .' · '.$evaluacion['etiqueta'].' · Estatus: '.$evaluacion['estatus']
                .' · Emitido: '.$institucional['generado'],
                1
            )],
            [],
            [XlsxWriter::texto(self::TITULO_HOJAS[$numeroHoja - 1], 2)],
            [],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function firmas(ReincoExportData $datos): array
    {
        $filas = [
            [],
            [XlsxWriter::texto('Día, mes y año: '.now()->format('d/m/Y'))],
            [],
            [XlsxWriter::texto('Firmas para archivo y cotejo DGB', 1)],
        ];

        foreach ($datos->presentacion->firmas as $firma) {
            $filas[] = [XlsxWriter::texto($firma.': ______________________________')];
        }
        $filas[] = [XlsxWriter::texto('Fecha de recepción: ______________________________')];

        return $filas;
    }

    /** @return array<int, array<int, mixed>> */
    private function hojaDatosGenerales(ReincoExportData $datos): array
    {
        $filas = $this->encabezado($datos, 1);
        $filas[] = [XlsxWriter::texto('Dato', 8), XlsxWriter::texto('Valor', 8)];

        $campos = [
            'Plantel' => $datos->institucional['plantel'],
            'CCT' => $datos->institucional['cct'],
            'Nivel' => $datos->institucional['nivel'],
            'Turno' => $datos->institucional['turno'],
            'Periodo escolar' => $datos->institucional['periodo_escolar'],
            'Semestre' => $datos->grupo['semestre'],
            'Grupo' => $datos->grupo['grupo'],
            'Plan / retícula' => $datos->grupo['plan_retricula'],
            'Área / componente' => $datos->grupo['area_componente'],
            'Materia' => $datos->materia['clave'].' · '.$datos->materia['nombre'],
            'Docente' => $datos->materia['docente'],
            'Personal SEP' => $datos->materia['sep'],
            'RFC' => $datos->materia['rfc'],
            'Evaluación' => $datos->evaluacion['etiqueta'],
            'Tipo de evidencia' => $datos->evaluacion['tipo'],
            'Fecha de examen' => $datos->evaluacion['fecha_examen'],
            'Fecha de entrega de proyecto' => $datos->evaluacion['fecha_entrega'],
            'Estatus' => $datos->evaluacion['estatus'],
            'Formato de calificación' => $datos->evaluacion['formato_calificacion'],
        ];

        foreach ($campos as $etiqueta => $valor) {
            $filas[] = [XlsxWriter::texto($etiqueta, 1), XlsxWriter::texto((string) $valor, 9)];
        }

        $filas[] = [];
        $filas[] = [XlsxWriter::texto('Evidencias configuradas del parcial', 1)];
        $filas[] = [
            XlsxWriter::texto('Núm.', 8),
            XlsxWriter::texto('Rubro', 8),
            XlsxWriter::texto('Tipo', 8),
            XlsxWriter::texto('Valor (%)', 8),
        ];
        foreach ($datos->evidencias as $evidencia) {
            $filas[] = [
                XlsxWriter::numero($evidencia->numero),
                XlsxWriter::texto($evidencia->rubro, 9),
                XlsxWriter::texto($evidencia->tipo, 9),
                XlsxWriter::numero($evidencia->porcentaje, 5),
            ];
        }
        $filas[] = [XlsxWriter::texto('Total', 1), XlsxWriter::texto(''), XlsxWriter::texto(''), XlsxWriter::numero((float) $datos->evidencias->sum('porcentaje'), 10)];
        $filas[] = [];
        $filas[] = [XlsxWriter::texto('Nota: los porcentajes de las evidencias suman hasta 100. La calificación final se reporta de 0 a 10.', 4)];

        return array_merge($filas, $this->firmas($datos));
    }

    /** @return array<int, array<int, mixed>> */
    private function hoja205(ReincoExportData $datos): array
    {
        $filas = $this->encabezado($datos, 2);
        $cabecera = [
            XlsxWriter::texto('Núm.', 8),
            XlsxWriter::texto('CURP (clave de control)', 8),
            XlsxWriter::texto('Nombre completo', 8),
        ];
        foreach ([1, 2, 3] as $parcial) {
            $cabecera[] = XlsxWriter::texto('Asist. P'.$parcial, 8);
            $cabecera[] = XlsxWriter::texto('Faltas P'.$parcial, 8);
            $cabecera[] = XlsxWriter::texto('% P'.$parcial, 8);
        }
        $cabecera = array_merge($cabecera, [
            XlsxWriter::texto('Asist. totales', 8),
            XlsxWriter::texto('Faltas totales', 8),
            XlsxWriter::texto('% asistencia', 8),
            XlsxWriter::texto('Observaciones', 8),
        ]);
        $filas[] = $cabecera;

        $totales = ['presente' => 0, 'falta' => 0, 'retardo' => 0, 'justificada' => 0];
        foreach ($datos->alumnos as $alumno) {
            $fila = [
                XlsxWriter::numero($alumno->numero, 7),
                XlsxWriter::texto($alumno->curp, 9),
                XlsxWriter::texto($alumno->nombreCompleto, 9),
            ];
            foreach ([1, 2, 3] as $parcial) {
                $datosParcial = $alumno->asistenciasPorParcial[$parcial];
                $fila[] = XlsxWriter::numero($datosParcial['presente'] + $datosParcial['retardo'] + $datosParcial['justificada'], 6);
                $fila[] = XlsxWriter::numero($datosParcial['falta'], 6);
                $fila[] = XlsxWriter::numero($datosParcial['porcentaje'], 5);
            }
            $general = $alumno->asistenciasPorParcial['general'];
            foreach (['presente', 'falta', 'retardo', 'justificada'] as $clave) {
                $totales[$clave] += $general[$clave];
            }
            $fila[] = XlsxWriter::numero($general['presente'] + $general['retardo'] + $general['justificada'], 6);
            $fila[] = XlsxWriter::numero($general['falta'], 6);
            $fila[] = XlsxWriter::numero($general['porcentaje'], 5);
            $fila[] = XlsxWriter::texto($alumno->observaciones, 9);
            $filas[] = $fila;
        }

        $registradas = array_sum($totales);
        $porcentajeGeneral = $registradas > 0
            ? round(($totales['presente'] + $totales['retardo'] + $totales['justificada']) / $registradas * 100, 2)
            : 0.0;
        $filas[] = array_merge(
            [XlsxWriter::texto('SUMAS', 1), XlsxWriter::texto(''), XlsxWriter::texto('')],
            array_fill(0, 9, XlsxWriter::texto('')),
            [
                XlsxWriter::numero($totales['presente'] + $totales['retardo'] + $totales['justificada'], 10),
                XlsxWriter::numero($totales['falta'], 10),
                XlsxWriter::numero($porcentajeGeneral, 10),
                XlsxWriter::texto(''),
            ]
        );

        return array_merge($filas, $this->firmas($datos));
    }

    /** @return array<int, array<int, mixed>> */
    private function hoja206A(ReincoExportData $datos): array
    {
        $filas = $this->encabezado($datos, 3);
        $cabecera = [
            XlsxWriter::texto('Núm.', 8),
            XlsxWriter::texto('CURP (clave de control)', 8),
            XlsxWriter::texto('Nombre completo', 8),
        ];
        foreach ($datos->evidencias as $evidencia) {
            $cabecera[] = XlsxWriter::texto(
                'Evidencia '.$evidencia->numero.': '.$evidencia->rubro.' ('.$evidencia->tipo.') '.$evidencia->porcentaje.'%',
                8
            );
        }
        $cabecera = array_merge($cabecera, [
            XlsxWriter::texto('Total evidencias', 8),
            XlsxWriter::texto('Porcentaje total', 8),
            XlsxWriter::texto('Fecha de entrega', 8),
        ]);
        $filas[] = $cabecera;

        foreach ($datos->alumnos as $alumno) {
            $fila = [
                XlsxWriter::numero($alumno->numero, 7),
                XlsxWriter::texto($alumno->curp, 9),
                XlsxWriter::texto($alumno->nombreCompleto, 9),
            ];
            $total = 0.0;
            foreach ($datos->evidencias as $evidencia) {
                $valor = $alumno->evidencias[$evidencia->id]['porcentaje'] ?? 0.0;
                $total += $valor;
                $fila[] = XlsxWriter::numero($valor, 5);
            }
            $fila[] = XlsxWriter::numero($datos->evidencias->count(), 6);
            $fila[] = XlsxWriter::numero(round($total, 2), 5);
            $fila[] = XlsxWriter::texto($datos->evidencias->first()?->fechaEntrega ?? $datos->evaluacion['fecha_entrega'], 9);
            $filas[] = $fila;
        }

        $filas[] = [];
        $filas[] = [XlsxWriter::texto('Porcentajes asignados: '.$datos->evidencias->sum('porcentaje').'% · Fecha de examen: '.$datos->evaluacion['fecha_examen'], 4)];
        $filas[] = [XlsxWriter::texto('Reverso: consulta la hoja «206-A reverso» para el detalle de cada evidencia.', 4)];

        return array_merge($filas, $this->firmas($datos));
    }

    /** @return array<int, array<int, mixed>> */
    private function reverso206A(ReincoExportData $datos): array
    {
        $filas = $this->encabezado($datos, 4);
        $filas[] = [
            XlsxWriter::texto('Núm.', 8),
            XlsxWriter::texto('Rubro', 8),
            XlsxWriter::texto('Tipo de evidencia', 8),
            XlsxWriter::texto('Valor (%)', 8),
            XlsxWriter::texto('Fecha de entrega', 8),
            XlsxWriter::texto('Fecha de examen', 8),
        ];

        foreach ($datos->evidencias as $evidencia) {
            $filas[] = [
                XlsxWriter::numero($evidencia->numero),
                XlsxWriter::texto($evidencia->rubro, 9),
                XlsxWriter::texto($evidencia->tipo, 9),
                XlsxWriter::numero($evidencia->porcentaje, 5),
                XlsxWriter::texto($evidencia->fechaEntrega ?: $datos->evaluacion['fecha_entrega'], 9),
                XlsxWriter::texto($datos->evaluacion['fecha_examen'], 9),
            ];
        }

        $filas[] = [XlsxWriter::texto('Total asignado', 1), XlsxWriter::texto(''), XlsxWriter::texto(''), XlsxWriter::numero((float) $datos->evidencias->sum('porcentaje'), 10)];
        $filas[] = [];

        return array_merge($filas, $this->firmas($datos));
    }

    /** @return array<int, array<int, mixed>> */
    private function hoja206B(ReincoExportData $datos): array
    {
        $filas = $this->encabezado($datos, 5);
        $cabecera = [
            XlsxWriter::texto('Núm.', 8),
            XlsxWriter::texto('CURP (clave de control)', 8),
            XlsxWriter::texto('Nombre completo', 8),
            XlsxWriter::texto('Asistencias', 8),
            XlsxWriter::texto('Faltas', 8),
            XlsxWriter::texto('% asistencia', 8),
        ];
        foreach ($datos->evidencias as $evidencia) {
            $cabecera[] = XlsxWriter::texto('E'.$evidencia->numero.' · '.$evidencia->rubro, 8);
        }
        $cabecera = array_merge($cabecera, [
            XlsxWriter::texto('Total porcentaje', 8),
            XlsxWriter::texto('Calificación final', 8),
            XlsxWriter::texto('Literal', 8),
            XlsxWriter::texto('Estatus', 8),
        ]);
        $filas[] = $cabecera;

        foreach ($datos->alumnos as $alumno) {
            $fila = [
                XlsxWriter::numero($alumno->numero, 7),
                XlsxWriter::texto($alumno->curp, 9),
                XlsxWriter::texto($alumno->nombreCompleto, 9),
            ];
            $general = $alumno->asistenciasPorParcial['general'];
            $fila[] = XlsxWriter::numero($general['presente'] + $general['retardo'] + $general['justificada'], 6);
            $fila[] = XlsxWriter::numero($general['falta'], 6);
            $fila[] = XlsxWriter::numero($general['porcentaje'], 5);

            $totalPorcentaje = 0.0;
            foreach ($datos->evidencias as $evidencia) {
                $nota = $alumno->evidencias[$evidencia->id] ?? ['puntaje' => 0.0, 'porcentaje' => 0.0];
                $totalPorcentaje += $nota['porcentaje'];
                $fila[] = XlsxWriter::numero($nota['puntaje'], 5);
            }
            $fila[] = XlsxWriter::numero(round($totalPorcentaje, 2), 5);
            $fila[] = XlsxWriter::numero($alumno->calificacionFinal, $datos->presentacion->estiloNumero(true));
            $fila[] = XlsxWriter::texto($alumno->examinado ? ($alumno->calificacionFinal >= 6 ? 'A' : 'NA') : '', 7);
            $fila[] = XlsxWriter::texto($datos->evaluacion['estatus'], 7);
            $filas[] = $fila;
        }

        $filas[] = [];
        $filas[] = [XlsxWriter::texto('DATOS ESTADÍSTICOS', 1)];
        foreach ([
            'Total de educandos al inicio' => 'inicio',
            'Total de educandos examinados' => 'examinados',
            'Total de educandos aprobados' => 'aprobados',
            'Total de educandos reprobados' => 'reprobados',
            'Número de clases en el parcial' => 'clases',
        ] as $etiqueta => $clave) {
            $filas[] = [XlsxWriter::texto($etiqueta, 1), XlsxWriter::numero($datos->estadisticas[$clave], 6)];
        }

        return array_merge($filas, $this->firmas($datos));
    }

    private function letraColumna(int $numero): string
    {
        $columna = '';
        while ($numero > 0) {
            $numero--;
            $columna = chr(65 + ($numero % 26)).$columna;
            $numero = intdiv($numero, 26);
        }

        return $columna === '' ? 'A' : $columna;
    }
}
