<?php

namespace Tests\Feature;

use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorTest extends TestCase
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
        $this->get(route('proveedores.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_create_proveedores(): void
    {
        $this->actingAs($this->cajero)
            ->get(route('proveedores.create'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_list_proveedores(): void
    {
        Proveedor::factory()->count(3)->create();

        $this->actingAs($this->administrativo)
            ->get(route('proveedores.index'))
            ->assertOk();
    }

    public function test_creating_a_proveedor_persists_it(): void
    {
        $datos = [
            'tipo_documento' => 'RUC',
            'numero_documento' => '20123456789',
            'nombre_razon_social' => 'Distribuidora SAC',
            'direccion' => 'Av. Industrial 456',
            'telefono' => '987654321',
            'email' => 'contacto@distribuidora.com',
        ];

        $response = $this->actingAs($this->administrativo)
            ->post(route('proveedores.store'), $datos);

        $proveedor = Proveedor::firstWhere('numero_documento', '20123456789');

        $response->assertRedirect(route('proveedores.show', $proveedor));
        $this->assertDatabaseHas('proveedores', ['numero_documento' => '20123456789', 'estado' => true]);
        $this->assertDatabaseHas('auditorias', ['accion' => 'proveedor.creado', 'auditable_id' => $proveedor->id]);
    }

    public function test_ruc_must_have_exactly_eleven_digits(): void
    {
        $this->actingAs($this->administrativo)
            ->post(route('proveedores.store'), [
                'tipo_documento' => 'RUC',
                'numero_documento' => '123',
                'nombre_razon_social' => 'Distribuidora SAC',
            ])
            ->assertSessionHasErrors('numero_documento');
    }

    public function test_duplicate_document_for_same_tipo_is_rejected(): void
    {
        Proveedor::factory()->create(['tipo_documento' => 'RUC', 'numero_documento' => '20123456789']);

        $this->actingAs($this->administrativo)
            ->post(route('proveedores.store'), [
                'tipo_documento' => 'RUC',
                'numero_documento' => '20123456789',
                'nombre_razon_social' => 'Otra Razón Social',
            ])
            ->assertSessionHasErrors('numero_documento');
    }

    public function test_deactivating_a_proveedor_sets_estado_false_without_deleting_it(): void
    {
        $proveedor = Proveedor::factory()->create(['estado' => true]);

        $this->actingAs($this->administrativo)
            ->delete(route('proveedores.destroy', $proveedor))
            ->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'estado' => false]);
    }

    public function test_activating_a_proveedor_sets_estado_true(): void
    {
        $proveedor = Proveedor::factory()->create(['estado' => false]);

        $this->actingAs($this->administrativo)
            ->patch(route('proveedores.activate', $proveedor))
            ->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'estado' => true]);
    }

    public function test_cajero_cannot_deactivate_a_proveedor(): void
    {
        $proveedor = Proveedor::factory()->create();

        $this->actingAs($this->cajero)
            ->delete(route('proveedores.destroy', $proveedor))
            ->assertForbidden();
    }
}
