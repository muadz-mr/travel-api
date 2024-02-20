<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTravelTest extends TestCase
{
    use RefreshDatabase;

    private $uri = '/api/v1/admin/travels';

    public function test_public_user_cannot_access_adding_travel(): void
    {
        $response = $this->postJson($this->uri);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_adding_travel(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));
        $response = $this->actingAs($user)->postJson($this->uri);

        $response->assertStatus(403);
    }

    public function test_saves_travel_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson($this->uri, [
            'name' => 'Travel name',
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($user)->postJson($this->uri, [
            'name' => 'Travel name',
            'description' => 'Travel description',
            'is_public' => true,
            'number_of_days' => 2,
        ]);

        $response->assertStatus(201);

        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Travel name']);
    }

    public function test_updates_travel_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/admin/travels/{$travel->id}", [
            'name' => 'Travel name',
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($user)->putJson("/api/v1/admin/travels/{$travel->id}", [
            'name' => 'Travel name update',
            'description' => 'Travel description',
            'is_public' => true,
            'number_of_days' => 2,
        ]);

        $response->assertStatus(200);

        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Travel name update']);
    }
}
