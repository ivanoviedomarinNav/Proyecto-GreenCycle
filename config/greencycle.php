<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reglas del vivero
    |--------------------------------------------------------------------------
    |
    | Valores de las reglas de negocio. El tiempo de enfriamiento se puede
    | reducir desde el .env para pruebas y demostraciones.
    |
    */

    'max_level' => 10,

    'max_health' => 100,

    'health_per_care' => 20,

    'care_cooldown_minutes' => (int) env('GREENCYCLE_CARE_COOLDOWN_MINUTES', 60),

];
