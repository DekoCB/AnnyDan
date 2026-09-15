<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;

    private User $contabilidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');

        $this->contabilidad = User::factory()->create();
        $this->contabilidad->assignRole('CONTABILIDAD');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('auditoria.index'))->assertRedirect(route('login'));
    }

    public function test_cajero_cannot_view_auditoria(): void
    {
        $this->actingAs($this->cajero)->get(route('auditoria.index'))->assertForbidden();
    }

    public function test_contabilidad_can_view_auditoria(): void
    {
        $this->actingAs($this->contabilidad)->get(route('auditoria.index'))->assertOk();
    }

    public function test_creating_a_cliente_leaves_an_auditable_trail(): void
    {
        $cliente = Cliente::factory()->create();

        Auditoria::factory()->create([
            'accion' => 'cliente.creado',
            'auditable_type' => Cliente::class,
            'auditable_id' => $cliente->id,
            'valores_despues' => ['nombre_razon_social' => $cliente->nombre_razon_social],
        ]);

        $registro = Auditoria::where('accion', 'cliente.creado')->first();

        $response = $this->actingAs($this->contabilidad)->get(route('auditoria.show', $registro));

        $response->assertOk();
        $response->assertSee('cliente.creado');
        $response->assertSee($cliente->nombre_razon_social);
    }

    public function test_index_can_be_filtered_by_accion(): void
    {
        Auditoria::factory()->create(['accion' => 'venta.creada']);
        Auditoria::factory()->create(['accion' => 'cliente.creado']);

        $response = $this->actingAs($this->contabilidad)->get(route('auditoria.index', ['accion' => 'venta']));

        $response->assertOk();
        $response->assertSee('venta.creada');
        $response->assertDontSee('cliente.creado');
    }

    public function test_no_route_exists_to_update_or_delete_an_auditoria_record(): void
    {
        $registro = Auditoria::factory()->create();

        // El único verbo registrado para esta URI es GET (show); cualquier
        // otro debe rechazarse a nivel de ruta, sin llegar nunca a un
        // controller capaz de modificar el registro.
        $this->actingAs($this->contabilidad)->patch("/auditoria/{$registro->id}")->assertMethodNotAllowed();
        $this->actingAs($this->contabilidad)->delete("/auditoria/{$registro->id}")->assertMethodNotAllowed();
        $this->actingAs($this->contabilidad)->put("/auditoria/{$registro->id}")->assertMethodNotAllowed();
    }
}
