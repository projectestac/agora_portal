<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            UserSeeder::class,
            ClientTypeSeeder::class,
            LocationSeeder::class,
            ClientSeeder::class,
            ServiceSeeder::class,
            ManagerSeeder::class,
            ModelTypeSeeder::class,
            InstanceSeeder::class,
            QuerySeeder::class,
            RequestTypeSeeder::class,
            RequestTypeServiceSeeder::class,
            RequestSeeder::class,
            RolesTableSeeder::class,
            LogSeeder::class,
//            MigrationSeeder::class,
        ]);
    }
}
