<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Formato oficial de boleta de calificaciones
    |--------------------------------------------------------------------------
    |
    | Plantilla sanitizada del formato DGB entregado por el plantel. Conserva
    | las cuatro hojas, las fórmulas y la configuración de impresión; los datos
    | personales del ciclo anterior fueron eliminados.
    |
    */

    'plantilla' => resource_path('boleta/plantilla.xlsx'),

    'plantel' => 'Escuela de Bachilleres Lic. Antonio María de Rivera',
    'cct' => '30EBH0389X',
    'localidad' => 'Xalapa',
    'zona' => 2,
    'modalidad' => 'ESC',
    'turno' => 'VESP',

    'director' => '',
    'subdirector' => '',

    /*
    | Columnas de la hoja DATOS/DATOS2 que alimentan la boleta imprimible.
    | La clave es la letra de la columna; el valor es la etiqueta oficial.
    */
    'columnas' => [
        'Y' => 'CNEyT1 · Ciencias naturales, experimentales y tecnología I',
        'AA' => 'C.S1 · Ciencias sociales I',
        'AC' => 'C.D1 · Cultura digital I',
        'AE' => 'P.M1 · Pensamiento matemático I',
        'AG' => 'LYC1 · Lengua y comunicación I',
        'AI' => 'ING1 · Inglés I',
        'AK' => 'PFyH1 · Pensamiento filosófico y humanidades',
        'AM' => 'L.D1 · Laboratorio de investigación',
        'AO' => 'C.A. · Currículum ampliado',
    ],

    /*
    | Sugerencias para preseleccionar la columna a partir del nombre de la
    | materia. El docente siempre puede cambiarla en el formulario.
    */
    'sugerencias' => [
        'lengua y comunicación' => 'AG',
        'literatura' => 'AG',
        'matemáticas' => 'AE',
        'biología' => 'Y',
        'química' => 'Y',
        'física' => 'Y',
        'ciencias naturales' => 'Y',
        'historia de méxico' => 'AA',
        'geografía' => 'AA',
        'ciencias sociales' => 'AA',
        'formación cívica' => 'AA',
        'educación y valores' => 'AA',
        'tecnologías de la información' => 'AC',
        'cultura digital' => 'AC',
        'capacitación en informática' => 'AC',
        'inglés' => 'AI',
        'filosofía' => 'AK',
        'lab. de investigación' => 'AM',
        'laboratorio de investigación' => 'AM',
        'actividad para escolar' => 'AO',
        'currículum ampliado' => 'AO',
        'temas selectos' => 'AO',
        'administración' => 'AO',
        'capacitación en turismo' => 'AO',
    ],
];
