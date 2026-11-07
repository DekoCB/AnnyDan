<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Copia de seguridad de la base de datos de la app de escritorio. Solo tiene
 * sentido con SQLite, donde todos los datos viven en un único archivo.
 */
class RespaldoController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function index(): View
    {
        $this->authorize('respaldo.exportar');

        return view('respaldo.index', ['disponible' => $this->esSqlite()]);
    }

    public function exportar(): BinaryFileResponse
    {
        $this->authorize('respaldo.exportar');
        abort_unless($this->esSqlite(), 404);

        $nombre = 'annydan-respaldo-'.now()->format('Ymd-His').'.sqlite';
        $ruta = sys_get_temp_dir().DIRECTORY_SEPARATOR.$nombre;

        // VACUUM INTO genera una copia consistente aunque la base esté en uso.
        DB::connection()->getPdo()->exec('VACUUM INTO '.DB::connection()->getPdo()->quote($ruta));

        $this->auditoria->registrar('respaldo.exportado');

        return response()->download($ruta, $nombre)->deleteFileAfterSend();
    }

    private function esSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
