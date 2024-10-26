<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database
        $this->seed();

        // Get admin credentials from the environment
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        
    }

    /** @test */
    public function testUserCanBeCreated()
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'password123',
            'name' => 'Test User',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(200)
                 ->assertJson(['data' => 'created']);
        
        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    }

    /** @test */
    public function testUserCanBeRetrieved()
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson(['data' => $user->toArray()]);
    }

    /** @test */
    public function testUserCanBeUpdated()
    {
        $user = User::factory()->create();

        $data = ['name' => 'Updated User'];

        $response = $this->putJson('/api/users/' . $user->id, $data);

        $response->assertStatus(200)
                 ->assertJson(['data' => 'updated']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User']);
    }

    /** @test */
    public function testUserCanBeDeleted()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson(['data' => 'User Deleted']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
