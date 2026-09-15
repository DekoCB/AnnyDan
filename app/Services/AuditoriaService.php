<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditoriaService
{
    /**
     * Registra un evento de auditoría. Se usa desde los Controllers/Services
     * que crean, modifican o anulan registros financieros o de negocio.
     *
     * @param  array<string, mixed>|null  $valoresAntes
     * @param  array<string, mixed>|null  $valoresDespues
     */
    public function registrar(
        string $accion,
        ?Model $auditable = null,
        ?array $valoresAntes = null,
        ?array $valoresDespues = null,
    ): Auditoria {
        return Auditoria::create([
            'user_id' => Request::user()?->id,
            'accion' => $accion,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'valores_antes' => $valoresAntes,
            'valores_despues' => $valoresDespues,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
        ]);
    }
}
