<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tasa de IGV
    |--------------------------------------------------------------------------
    |
    | Tasa aplicada sobre el subtotal de cada venta para calcular el impuesto.
    | Centralizada aquí para que ningún componente la duplique (ver sección
    | 17 del requerimiento: "Centraliza las reglas de cálculo").
    |
    */
    'tasa_igv' => (float) env('TASA_IGV', 0.18),

    /*
    |--------------------------------------------------------------------------
    | Serie de boletas
    |--------------------------------------------------------------------------
    |
    | Serie única usada para emitir boletas de venta (tipo 'boleta'). El
    | correlativo se lleva por separado para cada serie vía
    | CorrelativoService (clave 'boleta-{serie}'), preparado para admitir
    | series adicionales en el futuro (ej. sucursales) sin cambiar el
    | esquema — ver sección 12 del requerimiento.
    |
    */
    'serie_boleta' => env('SERIE_BOLETA', 'B001'),

];
