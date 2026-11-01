<?php

namespace Tests\Feature;

use App\Models\SeedType;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TreeApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedType(): SeedType
    {
        return SeedType::create([
            'name' => 'Árbol básico',
            'cuidados_por_nivel' => 5,
            'monedas_cosecha' => 10,
        ]);
    }

    private function treeFor(User $user, SeedType $seedType): Tree
    {
        return Tree::create([
            'user_id' => $user->id,
            'seed_type_id' => $seedType->id,
            'nivel' => 0,
            'salud' => 100,
            'progreso' => 0,
            'estado' => Tree::ACTIVE,
        ]);
    }

    public function test_tree_endpoints_require_authentication(): void
    {
        $tree = $this->treeFor(User::factory()->create(), $this->seedType());

        $this->getJson('/api/trees')->assertUnauthorized();
        $this->getJson('/api/trees/'.$tree->id)->assertUnauthorized();
        $this->postJson('/api/trees', ['seed_type_id' => $tree->seed_type_id])->assertUnauthorized();
    }

    public function test_a_user_can_plant_a_tree_with_server_defined_values(): void
    {
        $user = User::factory()->create();
        $seedType = $this->seedType();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/trees', ['seed_type_id' => $seedType->id]);

        $response
            ->assertCreated()
            ->assertJsonPath('level', 0)
            ->assertJsonPath('health', 100)
            ->assertJsonPath('progress', 0)
            ->assertJsonPath('status', Tree::ACTIVE)
            ->assertJsonPath('seed_type.name', 'Árbol básico');

        $this->assertDatabaseHas('trees', ['user_id' => $user->id, 'seed_type_id' => $seedType->id]);
    }

    public function test_the_client_cannot_define_internal_attributes(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $seedType = $this->seedType();
        Sanctum::actingAs($user);

        $this->postJson('/api/trees', [
            'seed_type_id' => $seedType->id,
            'user_id' => $other->id,
            'nivel' => 10,
            'salud' => 1,
            'progreso' => 4,
            'estado' => Tree::MATURE,
            'level' => 10,
            'health' => 1,
            'progress' => 4,
            'status' => Tree::MATURE,
        ])->assertCreated()
            ->assertJsonPath('level', 0)
            ->assertJsonPath('health', 100)
            ->assertJsonPath('progress', 0)
            ->assertJsonPath('status', Tree::ACTIVE);

        $this->assertDatabaseHas('trees', ['user_id' => $user->id, 'estado' => Tree::ACTIVE]);
        $this->assertDatabaseMissing('trees', ['user_id' => $other->id]);
    }

    public function test_planting_validates_the_seed_type(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/trees', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['seed_type_id']);

        $this->postJson('/api/trees', ['seed_type_id' => 999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['seed_type_id']);
    }

    public function test_index_lists_only_the_trees_of_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $seedType = $this->seedType();
        $mine = $this->treeFor($user, $seedType);
        $this->treeFor($other, $seedType);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/trees');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $mine->id);
    }

    public function test_index_returns_an_empty_list_when_there_are_no_trees(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/trees')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_show_returns_an_owned_tree(): void
    {
        $user = User::factory()->create();
        $tree = $this->treeFor($user, $this->seedType());
        Sanctum::actingAs($user);

        $this->getJson('/api/trees/'.$tree->id)
            ->assertOk()
            ->assertJsonPath('id', $tree->id)
            ->assertJsonPath('seed_type.name', 'Árbol básico');
    }

    public function test_show_forbids_access_to_a_tree_of_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $tree = $this->treeFor($owner, $this->seedType());
        Sanctum::actingAs($intruder);

        $this->getJson('/api/trees/'.$tree->id)
            ->assertForbidden()
            ->assertJsonMissingPath('level');
    }

    public function test_show_returns_404_for_a_missing_tree(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/trees/999')->assertNotFound();
    }
}
