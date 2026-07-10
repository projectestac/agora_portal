<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ClientServicesTest extends TestCase
{
    public function test_client_user_can_view_their_service_list(): void
    {
        // 1. Arrangement.
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\RolesTableSeeder::class);

        $service = \App\Models\Service::factory()->create([
            'name' => 'Moodle',
            'status' => 'active',
        ]);

        $client = \App\Models\Client::factory()->create([
            'name' => 'Centre 1',
            'dns' => 'centre-1',
            'code' => 'a0000001',
            'url_type' => 'standard',
            'status' => 'active',
            'visible' => 'yes',
        ]);

        \App\Models\Instance::factory()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'db_host' => 'localhost',
            'status' => 'active',
        ]);

        $user = User::where('email', 'a0000001@xtec.invalid')->first();

        // 2. Act & Assert.
        $response = $this->actingAs($user)->get('/myagora/instances');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['Moodle', 'Estat:', 'active']);
    }

    public function test_client_user_cannot_view_their_requests(): void
    {
        // 1. Arrangement.
        $user = User::where('email', 'a0000001@xtec.invalid')->first();

        // 2. Act & Assert.
        $response = $this->actingAs($user)->get('/myagora/requests');
        $response->assertStatus(200);
        $response->assertSee('has de ser gestor');
    }

    public function test_client_user_cannot_access_files(): void
    {
        // 1. Arrangement.
        $user = User::where('email', 'a0000001@xtec.invalid')->first();

        // 2. Act & Assert.
        $response = $this->actingAs($user)->get('/myagora/files');
        $response->assertStatus(403);
    }

    public function test_client_user_can_add_managers(): void
    {
        // 1. Arrangement.
        $userClient = User::where('email', 'a0000001@xtec.invalid')->first();
        $userManager = User::where('email', 'manager1@xtec.invalid')->first();

        $client = \App\Models\Client::where('code', 'a0000001')->first();

        \App\Models\Manager::factory()->create([
            'client_id' => $client->id,
            'user_id' => $userManager->id,
        ]);

        // 2. Act & Assert.
        $response = $this->actingAs($userClient)->get('/myagora/managers');
        $response->assertStatus(200);
        $response->assertSee('manager1@xtec.invalid');
        $response->assertSee('Afegeix');
    }

    public function test_client_user_can_access_logs(): void
    {
        // 1. Arrangement.
        $userClient = User::where('email', 'a0000001@xtec.invalid')->first();

        // 2. Act & Assert.
        $response = $this->actingAs($userClient)->get('/myagora/logs');
        $response->assertStatus(200);
        $response->assertSee('Descripció');
    }
}
