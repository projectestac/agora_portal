<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Add roles.
        $adminRole = Role::create(['name' => 'admin']);
        $clientRole = Role::create(['name' => 'client']);
        $managerRole = Role::create(['name' => 'manager']);
        Role::create(['name' => 'user']);

        // Assign users to roles.
        $admin = User::where('name', 'admin')->first();
        $admin->assignRole($adminRole);

        $clients = User::where('name', 'like', 'a00000%')->get();
        foreach ($clients as $client) {
            $client->assignRole($clientRole);
        }

        $managers = User::where('name', 'like', 'manager%')->get();
        foreach ($managers as $manager) {
            $manager->assignRole($managerRole);
        }

        // Assign permissions.
        $permission = Permission::create(['name' => 'Administrate site']);
        $permission->assignRole($adminRole);

        $permission = Permission::create(['name' => 'Manage own managers']);
        $permission->assignRole($clientRole);

        $permission = Permission::create(['name' => 'Manage clients']);
        $permission->assignRole($managerRole);
    }
}
