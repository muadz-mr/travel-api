<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Travel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;

    private function generateUri(string $routeParameter): string
    {
        return "/api/v1/admin/travels/{$routeParameter}/tours";
    }

    public function test_public_user_cannot_access_adding_tour(): void
    {
        $travel = Travel::factory()->create();
        $response = $this->postJson($this->generateUri($travel->id));

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_adding_tour(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson($this->generateUri($travel->id));

        $response->assertStatus(403);
    }

    public function test_saves_tour_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson($this->generateUri($travel->id), [
            'name' => 'Tour name',
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($user)->postJson($this->generateUri($travel->id), [
            'name' => 'Tour name',
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDay()->toDateString(),
            'price' => 99.90,
        ]);

        $response->assertStatus(201);

        $response = $this->get("/api/v1/travels/{$travel->slug}/tours");
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Tour name']);
    }
}
