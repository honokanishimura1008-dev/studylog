<?php

namespace Tests\Unit;

use App\Models\Material;
use App\Models\User;
use App\Services\MaterialResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialResolverTest extends TestCase
{
    use RefreshDatabase;

    private MaterialResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new MaterialResolver();
    }

    public function test_returns_existing_material_id(): void
    {
        $user = User::factory()->create();
        $material = new Material([
            'user_id' => $user->id,
            'title' => 'ベンチプレス',
            'type' => 'chest',
        ]);
        $material->save();

        $result = $this->resolver->resolve($user, [
            'material_id' => $material->id,
        ]);

        $this->assertSame($material->id, $result);
    }

    public function test_creates_new_material_from_catalog(): void
    {
        $user = User::factory()->create();

        $result = $this->resolver->resolve($user, [
            'catalog_name' => 'レッグプレス',
            'catalog_type' => 'legs',
        ]);

        $this->assertDatabaseHas('materials', [
            'user_id' => $user->id,
            'title' => 'レッグプレス',
            'type' => 'legs',
            'cover_path' => 'images/materials/レッグプレス.png',
        ]);

        $this->assertIsInt($result);
    }

    public function test_uses_default_type_when_catalog_type_not_provided(): void
    {
        $user = User::factory()->create();

        $this->resolver->resolve($user, [
            'catalog_name' => 'スクワット',
        ]);

        $this->assertDatabaseHas('materials', [
            'user_id' => $user->id,
            'title' => 'スクワット',
            'type' => 'legs', // デフォルト値
        ]);
    }

    public function test_returns_existing_material_instead_of_creating_duplicate(): void
    {
        $user = User::factory()->create();
        $existingMaterial = new Material([
            'user_id' => $user->id,
            'title' => 'デッドリフト',
            'type' => 'back',
        ]);
        $existingMaterial->save();

        $result = $this->resolver->resolve($user, [
            'catalog_name' => 'デッドリフト',
            'catalog_type' => 'back',
        ]);

        $this->assertSame($existingMaterial->id, $result);
        $this->assertCount(1, Material::where('title', 'デッドリフト')->get());
    }
}
