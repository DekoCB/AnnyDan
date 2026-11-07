<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RespaldoTest extends TestCase
{
    use DatabaseMigrations;

    private function usuarioConRol(Rol $rol): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($rol->value));

        return $user;
    }

    public function test_administrador_puede_descargar_un_respaldo_y_queda_auditado(): void
    {
        $response = $this->actingAs($this->usuarioConRol(Rol::ADMINISTRADOR))->get(route('respaldo.exportar'));

        $response->assertOk();
        $this->assertStringContainsString('annydan-respaldo-', $response->headers->get('content-disposition'));
        $this->assertSame(1, Auditoria::where('accion', 'respaldo.exportado')->count());
    }

    public function test_cajero_no_puede_ver_ni_descargar_respaldos(): void
    {
        $cajero = $this->usuarioConRol(Rol::CAJERO);

        $this->actingAs($cajero)->get(route('respaldo.index'))->assertForbidden();
        $this->actingAs($cajero)->get(route('respaldo.exportar'))->assertForbidden();
    }
}
