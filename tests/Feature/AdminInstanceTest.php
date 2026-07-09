<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInstanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_view_instance_list(): void
    {
        // 1. Arrangement.
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\RolesTableSeeder::class);

        $testClient = \App\Models\Client::factory()->create([
            'name' => 'Centre 1',
            'dns' => 'centre-1',
            'url_type' => 'standard',
            'status' => 'active',
            'visible' => 'yes',
        ]);

        \App\Models\Instance::factory()->create([
            'client_id' => $testClient->id,
            'db_host' => 'localhost',
        ]);

        $admin = User::where('email', 'admin@xtec.invalid')->first();

        // 2. Act & Assert.
        $response = $this->actingAs($admin)->get('/instances');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->getJson('/instances/list?draw=1&start=0&length=10&search[value]=');
        $response->assertStatus(200);
        $response->assertSee('Centre 1');
    }
}
