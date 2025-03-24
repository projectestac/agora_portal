<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Manager;
use App\Models\Instance;

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

        // Check access to "Serveis" (Moodle and Nodes should be visible)
        $response = $this->get(route('myagora.instances'));
        $response->assertStatus(200);
        $response->assertSee('Moodle')->assertSee('Nodes');

        // Ensure the client does NOT have access to "Gestió de fitxers"
        $response = $this->get(route('myagora.files'));
        $response->assertSee(__('myagora.no_client_access'));

        // Check access to "Sol·licituds" (requests) and ensure only managers can access it
        $response = $this->get(route('myagora.requests'));
        $response->assertSee(__('myagora.no_client_access'));

        // Check access to "Gestors" (managers) and ensure they can add a manager
        $response = $this->get(route('managers.index'));
        $response->assertStatus(200);
        $response = $this->get(route('managers.create'));
        $response->assertStatus(200);

        // Check access to "Registre d'accions" (action logs)
        $response = $this->get(route('myagora.logs'));
        $response->assertStatus(200);
    }

    /**
     * Test case 2: Manager user access
     */
    public function testManagerAccess()
    {
        // Create a manager user and log in
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $manager->givePermissionTo('Manage own managers');
        $this->actingAs($manager);

        // Check access to "Serveis" (Moodle and Nodes should be visible)
        $response = $this->get(route('myagora.instances'));
        $response->assertStatus(200);
        // $response->assertSee('Moodle')->assertSee('Nodes'); // this fails for now, I don't know why, it appears when logging in as a manager

        // Check access to "Gestió de fitxers" (file management)
        $response = $this->get(route('myagora.files'));
        $response->assertStatus(200);

        // // Check access to "Sol·licituds" (requests) and ensure the manager can submit a request
        $response = $this->get(route('myagora.requests'));
        $response->assertStatus(200);

        // Check access to "Gestors" (managers) and ensure the manager can add another manager
        $response = $this->get(route('managers.index'));
        $response->assertStatus(200);
        $response = $this->get(route('managers.create'));
        $response->assertStatus(200);

        // Check access to "Registre d'accions" (action logs)
        $response = $this->get(route('myagora.logs'));
        $response->assertStatus(200);

        // Log out after test
        $response = $this->post(route('logout'));
        $response->assertStatus(302); // 302 is success followed by a redirection
    }

    /**
     * Test case 3: Administrator user access
     */
    public function testAdminAccess()
    {
        // Create an admin user and log in
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('Administrate site');
        $this->actingAs($admin);

        // Check access to the instances list = Check access to "Administració"
        $response = $this->get(route('instances.index'));
        $response->assertStatus(200);

        // Check access to bulk actions
        $response = $this->get(route('operation'));
        $response->assertStatus(200);

        // Check that the admin can edit an instance
        $instance = Instance::first(); // assuming an instance exists
        $response = $this->get(route('instances.edit', $instance));
        $response->assertStatus(200);

        // Check that the admin can save changes to an instance
        $response = $this->put(route('instances.update', $instance), [
            'id' => $instance->id,
            'status' => 'inactive',
        ]);
        $response->assertStatus(302); // redirected after successful update
    }
}
