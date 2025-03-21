<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Manager;

class UserAccessTest extends TestCase
{
    /**
     * Test case 1: Client user access
     */
    public function testClientAccess()
    {
        // Create a client user and log in
        $client = User::factory()->create();
        $client->assignRole('client');
        $client->givePermissionTo('Manage clients');

        // The client is also a manager
        $manager = new Manager([
            'user_id' => $client->id,
            'client_id' => 1,
        ]);
        $manager->save();

        $this->actingAs($client);

        // Check access to "El meu Àgora"
        // $response = $this->get(route('myagora'));
        // $response->assertStatus(200);

        // // Check access to "Serveis" (Moodle and Nodes should be visible)
        $response = $this->get(route('myagora.instances'));
        $response->assertStatus(200);
        $response->assertSee('Moodle')->assertSee('Nodes');

        // Ensure the client does NOT have access to "Gestió de fitxers"
        // $response = $this->get(route('myagora.files'));
        // $response->assertStatus(403);

        // Check access to "Sol·licituds" (requests) and ensure only managers can access it
        // $response = $this->get(route('myagora.requests'));
        // $response->assertSee('Only managers can access this section');

        // Check access to "Gestors" (managers) and ensure they can add a manager
        $response = $this->get(route('managers.index'));
        $response->assertStatus(200);

        // // Check access to "Registre d'accions" (action logs)
        $response = $this->get(route('myagora.logs'));
        $response->assertStatus(200);
    }
}
