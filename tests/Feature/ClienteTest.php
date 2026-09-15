<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private User $administrativo;

    private User $cajero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->administrativo = User::factory()->create();
        $this->administrativo->assignRole('ADMINISTRATIVO');

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('clientes.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_create_clientes(): void
    {
        $this->actingAs($this->cajero)
            ->get(route('clientes.create'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_list_clientes(): void
    {
        Cliente::factory()->count(3)->create();

        $this->actingAs($this->administrativo)
            ->get(route('clientes.index'))
            ->assertOk();
    }

    public function test_creating_a_cliente_persists_it(): void
    {
        $datos = [
            'tipo_documento' => 'DNI',
            'numero_documento' => '12345678',
            'nombre_razon_social' => 'Juan Pérez',
            'direccion' => 'Av. Siempre Viva 123',
            'telefono' => '987654321',
            'email' => 'juan@example.com',
        ];

        $response = $this->actingAs($this->administrativo)
            ->post(route('clientes.store'), $datos);

        $cliente = Cliente::firstWhere('numero_documento', '12345678');

        $response->assertRedirect(route('clientes.show', $cliente));
        $this->assertDatabaseHas('clientes', ['numero_documento' => '12345678', 'estado' => true]);
        $this->assertDatabaseHas('auditorias', ['accion' => 'cliente.creado', 'auditable_id' => $cliente->id]);
    }

    public function test_dni_must_have_exactly_eight_digits(): void
    {
        $this->actingAs($this->administrativo)
            ->post(route('clientes.store'), [
                'tipo_documento' => 'DNI',
                'numero_documento' => '123',
                'nombre_razon_social' => 'Juan Pérez',
            ])
            ->assertSessionHasErrors('numero_documento');
    }

    public function test_duplicate_document_for_same_tipo_is_rejected(): void
    {
        Cliente::factory()->create(['tipo_documento' => 'DNI', 'numero_documento' => '12345678']);

        $this->actingAs($this->administrativo)
            ->post(route('clientes.store'), [
                'tipo_documento' => 'DNI',
                'numero_documento' => '12345678',
                'nombre_razon_social' => 'Otro Nombre',
            ])
            ->assertSessionHasErrors('numero_documento');
    }

    public function test_deactivating_a_cliente_sets_estado_false_without_deleting_it(): void
    {
        $cliente = Cliente::factory()->create(['estado' => true]);

        $this->actingAs($this->administrativo)
            ->delete(route('clientes.destroy', $cliente))
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'estado' => false]);
    }

    public function test_activating_a_cliente_sets_estado_true(): void
    {
        $cliente = Cliente::factory()->create(['estado' => false]);

        $this->actingAs($this->administrativo)
            ->patch(route('clientes.activate', $cliente))
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'estado' => true]);
    }

    public function test_cajero_cannot_deactivate_a_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $this->actingAs($this->cajero)
            ->delete(route('clientes.destroy', $cliente))
            ->assertForbidden();
    }
}
