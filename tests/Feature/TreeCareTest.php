<?php

namespace Tests\Feature;

use App\Models\SeedType;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TreeCareTest extends TestCase
{
    use RefreshDatabase;

    private function seedType(int $careForLevel = 5): SeedType
    {
        return SeedType::create([
            'name' => 'Árbol de prueba',
            'cuidados_por_nivel' => $careForLevel,
            'monedas_cosecha' => 10,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function treeFor(User $user, array $attributes = [], ?SeedType $seedType = null): Tree
    {
        return Tree::create(array_merge([
            'user_id' => $user->id,
            'seed_type_id' => ($seedType ?? $this->seedType())->id,
            'nivel' => 0,
            'salud' => 100,
            'progreso' => 0,
            'estado' => Tree::ACTIVE,
        ], $attributes));
    }

    private function care(Tree $tree)
    {
        return $this->postJson('/api/trees/'.$tree->id.'/care');
    }

    // 1. Cuidado exitoso -------------------------------------------------

    public function test_a_valid_care_increases_progress_and_sets_the_next_care_date(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user);
        Sanctum::actingAs($user);

        $expectedNextCare = now()->addMinutes(60);

        $this->care($tree)
            ->assertOk()
            ->assertExactJsonStructure([
                'message',
                'tree' => ['id', 'seed_type', 'level', 'health', 'progress', 'status', 'last_cared_at', 'next_care_at', 'harvested_at', 'created_at'],
            ])
            ->assertJsonPath('message', 'Cuidado aplicado con éxito')
            ->assertJsonPath('tree.id', $tree->id)
            ->assertJsonPath('tree.level', 0)
            ->assertJsonPath('tree.progress', 1)
            ->assertJsonPath('tree.status', Tree::ACTIVE)
            ->assertJsonPath('tree.next_care_at', $expectedNextCare->toIso8601ZuluString());

        $tree->refresh();
        $this->assertSame(1, $tree->progreso);
        // La base guarda segundos: se compara con esa precisión.
        $this->assertSame(now()->toIso8601ZuluString(), $tree->last_cared_at->toIso8601ZuluString());
        $this->assertSame($expectedNextCare->toIso8601ZuluString(), $tree->next_care_at->toIso8601ZuluString());
    }

    public function test_care_recovers_20_health_without_exceeding_100(): void
    {
        $user = User::factory()->create();
        $hurt = $this->treeFor($user, ['salud' => 50]);
        $almostFull = $this->treeFor($user, ['salud' => 90]);
        Sanctum::actingAs($user);

        $this->care($hurt)->assertOk()->assertJsonPath('tree.health', 70);
        $this->care($almostFull)->assertOk()->assertJsonPath('tree.health', 100);
    }

    public function test_care_ignores_any_value_sent_by_the_client(): void
    {
        $user = User::factory()->create();
        $tree = $this->treeFor($user);
        Sanctum::actingAs($user);

        $this->postJson('/api/trees/'.$tree->id.'/care', [
            'level' => 10,
            'health' => 1,
            'progress' => 4,
            'status' => Tree::MATURE,
            'next_care_at' => now()->subYear()->toIso8601ZuluString(),
        ])->assertOk()
            ->assertJsonPath('tree.level', 0)
            ->assertJsonPath('tree.progress', 1)
            ->assertJsonPath('tree.status', Tree::ACTIVE);
    }

    public function test_a_tree_can_be_cared_again_exactly_when_the_cooldown_ends(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user);
        Sanctum::actingAs($user);

        $this->care($tree)->assertOk();
        $this->travelTo($tree->fresh()->next_care_at);

        $this->care($tree)->assertOk()->assertJsonPath('tree.progress', 2);
    }

    public function test_the_cooldown_duration_is_configurable(): void
    {
        $this->freezeTime();
        config(['greencycle.care_cooldown_minutes' => 5]);
        $user = User::factory()->create();
        $tree = $this->treeFor($user);
        Sanctum::actingAs($user);

        $this->care($tree)
            ->assertOk()
            ->assertJsonPath('tree.next_care_at', now()->addMinutes(5)->toIso8601ZuluString());
    }

    // 2. Bloqueo por cooldown --------------------------------------------

    public function test_a_second_consecutive_care_is_rejected_by_the_cooldown_without_changes(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user, ['salud' => 50]);
        Sanctum::actingAs($user);

        $this->care($tree)->assertOk();
        $afterFirstCare = $tree->fresh();

        $this->care($tree)
            ->assertStatus(409)
            ->assertJson([
                'error' => 'El árbol se encuentra en período de enfriamiento.',
                'code' => 'TREE_ON_COOLDOWN',
                'next_care_at' => now()->addMinutes(60)->toIso8601ZuluString(),
            ]);

        $tree->refresh();
        $this->assertSame(1, $tree->progreso);
        $this->assertSame(70, $tree->salud);
        $this->assertSame(
            $afterFirstCare->next_care_at->toIso8601ZuluString(),
            $tree->next_care_at->toIso8601ZuluString(),
        );
    }

    // 3. Ascenso de nivel ------------------------------------------------

    public function test_five_valid_cares_raise_the_level_and_reset_the_progress(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user);
        Sanctum::actingAs($user);

        foreach (range(1, 4) as $expectedProgress) {
            $this->care($tree)->assertOk()->assertJsonPath('tree.progress', $expectedProgress);
            $this->travel(61)->minutes();
        }

        $this->care($tree)
            ->assertOk()
            ->assertJsonPath('tree.level', 1)
            ->assertJsonPath('tree.progress', 0)
            ->assertJsonPath('tree.status', Tree::ACTIVE);

        $tree->refresh();
        $this->assertSame(1, $tree->nivel);
        $this->assertSame(0, $tree->progreso);
    }

    public function test_a_special_seed_needs_fewer_cares_per_level(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user, [], $this->seedType(3));
        Sanctum::actingAs($user);

        foreach (range(1, 2) as $ignored) {
            $this->care($tree)->assertOk();
            $this->travel(61)->minutes();
        }

        $this->care($tree)
            ->assertOk()
            ->assertJsonPath('tree.level', 1)
            ->assertJsonPath('tree.progress', 0);
    }

    // 4. Evolución a MATURE ----------------------------------------------

    public function test_reaching_level_10_turns_the_tree_into_mature(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user, ['nivel' => 9, 'progreso' => 4]);
        Sanctum::actingAs($user);

        $this->care($tree)
            ->assertOk()
            ->assertJsonPath('tree.level', 10)
            ->assertJsonPath('tree.progress', 0)
            ->assertJsonPath('tree.status', Tree::MATURE);

        $this->assertSame(Tree::MATURE, $tree->fresh()->estado);
    }

    // 5. Protección de recursos cruzados ---------------------------------

    public function test_a_user_cannot_care_for_a_tree_of_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $tree = $this->treeFor($owner);
        Sanctum::actingAs($intruder);

        $this->care($tree)->assertForbidden();

        $tree->refresh();
        $this->assertSame(0, $tree->progreso);
        $this->assertNull($tree->next_care_at);
    }

    // Estados terminales y casos límite ----------------------------------

    /**
     * @return array<string, array{string, string}>
     */
    public static function terminalStatuses(): array
    {
        return [
            'muerto' => [Tree::DEAD, 'El árbol está muerto y ya no puede recibir cuidados.'],
            'maduro' => [Tree::MATURE, 'El árbol ya alcanzó la madurez y no puede recibir más cuidados.'],
            'cosechado' => [Tree::HARVESTED, 'El árbol ya fue cosechado y no puede recibir cuidados.'],
        ];
    }

    #[DataProvider('terminalStatuses')]
    public function test_a_tree_in_a_terminal_status_cannot_be_cared(string $status, string $message): void
    {
        $user = User::factory()->create();
        $tree = $this->treeFor($user, ['estado' => $status, 'salud' => $status === Tree::DEAD ? 0 : 100]);
        Sanctum::actingAs($user);

        $this->care($tree)
            ->assertStatus(409)
            ->assertJson([
                'error' => $message,
                'code' => 'TREE_NOT_CAREABLE',
                'status' => $status,
            ]);

        $tree->refresh();
        $this->assertSame($status, $tree->estado);
        $this->assertSame(0, $tree->progreso);
        $this->assertNull($tree->next_care_at);
    }

    public function test_a_mature_tree_cannot_receive_more_care_after_evolving(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $tree = $this->treeFor($user, ['nivel' => 9, 'progreso' => 4]);
        Sanctum::actingAs($user);

        $this->care($tree)->assertOk();
        $this->travel(61)->minutes();

        $this->care($tree)->assertStatus(409)->assertJsonPath('code', 'TREE_NOT_CAREABLE');
    }

    public function test_the_terminal_status_is_checked_before_the_cooldown(): void
    {
        $user = User::factory()->create();
        $tree = $this->treeFor($user, [
            'estado' => Tree::DEAD,
            'salud' => 0,
            'next_care_at' => now()->addHour(),
        ]);
        Sanctum::actingAs($user);

        $this->care($tree)->assertStatus(409)->assertJsonPath('code', 'TREE_NOT_CAREABLE');
    }

    public function test_care_requires_authentication(): void
    {
        $tree = $this->treeFor(User::factory()->create());

        $this->care($tree)->assertUnauthorized();
    }

    public function test_care_returns_404_for_a_missing_tree(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/trees/999/care')->assertNotFound();
    }
}
