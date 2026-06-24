<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->material = Material::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'ベンチプレス',
            'type' => 'chest',
        ]);
    }

    public function test_store_creates_weekly_menu(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('menu.store'), [
            'dow' => 'mon',
            'sort_order' => 1,
            'material_id' => $this->material->id,
            'sets' => 4,
            'reps' => '8-12',
            'memo' => '軽めに',
        ]);

        $response->assertCreated()
            ->assertJsonPath('menu.sets', 4)
            ->assertJsonPath('menu.repDisplay', '8-12');

        $this->assertDatabaseHas('weekly_menus', [
            'user_id' => $this->user->id,
            'dow' => 'mon',
            'sets' => 4,
            'rep_min' => 8,
            'rep_max' => 12,
        ]);
    }

    public function test_update_modifies_existing_menu(): void
    {
        $menu = WeeklyMenu::factory()->create([
            'user_id' => $this->user->id,
            'material_id' => $this->material->id,
            'dow' => 'tue',
            'sets' => 3,
            'rep_min' => 10,
            'rep_max' => 10,
        ]);

        $response = $this->actingAs($this->user)->putJson(
            route('menu.update', ['weeklyMenu' => $menu]),
            [
                'dow' => 'wed',
                'sort_order' => 2,
                'material_id' => $this->material->id,
                'sets' => 5,
                'reps' => '5',
                'memo' => null,
            ]
        );

        $response->assertOk()
            ->assertJsonPath('menu.dow', 'wed')
            ->assertJsonPath('menu.setsRepDisplay', '5 × 5');

        $this->assertDatabaseHas('weekly_menus', [
            'id' => $menu->id,
            'dow' => 'wed',
            'sets' => 5,
            'rep_min' => 5,
            'rep_max' => 5,
        ]);
    }

    public function test_destroy_deletes_menu(): void
    {
        $menu = WeeklyMenu::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            route('menu.destroy', ['weeklyMenu' => $menu])
        );

        $response->assertOk();
        $this->assertModelMissing($menu);
    }

    public function test_cannot_update_others_menu(): void
    {
        $otherUser = User::factory()->create();
        $menu = WeeklyMenu::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->putJson(
            route('menu.update', ['weeklyMenu' => $menu]),
            ['dow' => 'mon', 'sort_order' => 1, 'material_id' => $this->material->id, 'sets' => 3, 'reps' => '10']
        );

        $response->assertForbidden();
    }

    public function test_catalog_creates_material_if_not_exists(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('menu.store'), [
            'dow' => 'fri',
            'sort_order' => 1,
            'catalog_name' => 'レッグプレス',
            'catalog_type' => 'legs',
            'sets' => 4,
            'reps' => '12-15',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('materials', [
            'user_id' => $this->user->id,
            'title' => 'レッグプレス',
            'type' => 'legs',
        ]);
    }
}
