<?php

namespace App\Enums;

enum Rol: string
{
    case ADMINISTRADOR = 'ADMINISTRADOR';
    case ADMINISTRATIVO = 'ADMINISTRATIVO';
    case CAJERO = 'CAJERO';
    case CONTABILIDAD = 'CONTABILIDAD';
}
