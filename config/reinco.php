<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Datos institucionales del formato REINCO
    |--------------------------------------------------------------------------
    |
    | Se usan en el encabezado de todas las hojas. Pueden sobreescribirse al
    | generar un formato desde el portal docente.
    |
    */

    'plantel' => 'Escuela de Bachilleres Lic. Antonio María de Rivera',
    'cct' => '30EBH0389X',
    'nivel' => 'Bachillerato',
    'turno' => 'Vespertino',
    'periodo_escolar' => null,

    /*
    |--------------------------------------------------------------------------
    | Evaluación
    |--------------------------------------------------------------------------
    |
    | estatus: ordinaria, final, R1, R2 o R3.
    | formato_calificacion: entero (0) o decimal (0.00).
    |
    */

    'estatus' => 'ordinaria',
    'formato_calificacion' => 'decimal',

    /*
    |--------------------------------------------------------------------------
    | Presentación del archivo
    |--------------------------------------------------------------------------
    |
    | Configuración de impresión aplicada a cada hoja. La orientación horizontal
    | y el ajuste a una página de ancho evitan que se corten columnas.
    |
    */

    'orientacion' => 'landscape',
    'papel' => 9, // A4
    'ajustar_ancho' => 1,
    'ajustar_alto' => 0,
    'margenes' => [
        'left' => 0.25,
        'right' => 0.25,
        'top' => 0.45,
        'bottom' => 0.45,
        'header' => 0.2,
        'footer' => 0.2,
    ],
    'centrar_horizontal' => true,

    /*
     * Si se coloca una plantilla en esta ruta, el escritor toma de ella la
     * configuración de página (printOptions, pageMargins y pageSetup).
     * Si no existe, la hoja se construye ordenada y tipada desde cero.
     */
    'plantilla_base' => resource_path('reinco/plantilla.xlsx'),
];
