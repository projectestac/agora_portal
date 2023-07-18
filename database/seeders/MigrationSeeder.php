<?php

namespace Database\Seeders;

use App\Models\Instance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrationSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        // Users migration.
        $users = DB::connection('agoraportal')
            ->table('users')
            ->select(['uid', 'uname', 'email', 'pass', 'user_regdate', 'lastlogin', 'approved_date'])
            ->get();

        foreach ($users as $user) {
            // MySQL doesn't accept '1970-01-01 00:00:00' as a valid date, so we need to change it to '1970-01-02 00:00:00'.
            $lastLogin = ($user->lastlogin === '1970-01-01 00:00:00') ? '1970-01-02 00:00:00' : $user->lastlogin;
            $userRegDate = ($user->user_regdate === '1970-01-01 00:00:00') ? '1970-01-02 00:00:00' : $user->user_regdate;

            DB::table('users')->insert([
                'id' => $user->uid,
                'name' => $user->uname,
                'email' => $user->email,
                'password' => substr($user->pass, 3),
                'last_login_at' => $lastLogin,
                'created_at' => $userRegDate,
                'updated_at' => $userRegDate,
            ]);
        }

        // Groups migration.
        $groups = DB::connection('agoraportal')
            ->table('groups')
            ->select(['gid', 'name'])
            ->get();

        foreach ($groups as $group) {
            $replacement = [
                'Users' => 'user',
                'Administrators' => 'admin',
                'Clients' => 'client',
                'Managers' => 'manager',
            ];

            DB::table('roles')->insert([
                'id' => $group->gid,
                'name' => $replacement[$group->name],
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Group membership migration.
        $membership = DB::connection('agoraportal')
            ->table('group_membership')
            ->select(['gid', 'uid'])
            ->get();

        foreach ($membership as $member) {
            DB::table('model_has_roles')->insert([
                'role_id' => $member->gid,
                'model_type' => 'App\Models\User',
                'model_id' => $member->uid,
            ]);
        }

        // Client types migration.
        $clientTypes = DB::connection('agoraportal')
            ->table('agoraportal_clientType')
            ->select(['typeId', 'typeName'])
            ->get();

        foreach ($clientTypes as $clientType) {
            DB::table('client_types')->insert([
                'id' => $clientType->typeId,
                'name' => $clientType->typeName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Services migration.
        $services = DB::connection('agoraportal')
            ->table('agoraportal_services')
            ->select(['serviceId', 'serviceName', 'URL', 'description', 'defaultDiskSpace'])
            ->get();

        foreach ($services as $service) {
            DB::table('services')->insert([
                'id' => $service->serviceId,
                'name' => $service->serviceName,
                'status' => 'active',
                'description' => $service->description,
                'slug' => $service->URL,
                'quota' => $service->defaultDiskSpace * 1024 * 1024,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Locations migration.
        $locations = DB::connection('agoraportal')
            ->table('agoraportal_location')
            ->select(['locationId', 'locationName'])
            ->get();

        foreach ($locations as $location) {
            DB::table('locations')->insert([
                'id' => $location->locationId,
                'name' => $location->locationName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Model types migration.
        $data = [
            [
                'id' => 1,
                'service_id' => 4,
                'shortcode' => 'pri',
                'description' => 'Maqueta primària',
            ],
            [
                'id' => 2,
                'service_id' => 4,
                'shortcode' => 'sec',
                'description' => 'Maqueta secundària',
            ],
        ];

        foreach ($data as $item) {
            DB::table('model_types')->insert([
                'id' => $item['id'],
                'service_id' => $item['service_id'],
                'short_code' => $item['shortcode'],
                'description' => $item['description'],
                'url' => '',
                'db' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $modelTypes = DB::connection('agoraportal')
            ->table('agoraportal_modelTypes')
            ->select(['modelTypeId', 'shortcode', 'description', 'url', 'dbHost'])
            ->get();

        foreach ($modelTypes as $modelType) {
            DB::table('model_types')->insert([
                'id' => $modelType->modelTypeId,
                'service_id' => 5,
                'short_code' => $modelType->shortcode,
                'description' => $modelType->description,
                'url' => $modelType->url,
                'db' => $modelType->dbHost,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Clients migration.
        $clients = DB::connection('agoraportal')
            ->table('agoraportal_clients')
            ->select(['clientId', 'clientCode', 'clientDNS', 'clientOldDNS', 'URLType', 'URLHost', 'OldURLHost', 'clientName',
                'clientAddress', 'clientCity', 'clientPC', 'clientCountry', 'clientDescription', 'clientState', 'locationId',
                'typeId', 'noVisible', 'extraFunc'])
            ->get();

        foreach ($clients as $client) {
            DB::table('clients')->insert([
                'id' => $client->clientId,
                'code' => $client->clientCode,
                'name' => $client->clientName,
                'dns' => $client->clientDNS,
                'old_dns' => $client->clientOldDNS,
                'url_type' => $client->URLType,
                // 'host' => $client->URLHost,
                // 'old_host' => $client->OldURLHost,
                'address' => $client->clientAddress,
                'city' => $client->clientCity,
                'postal_code' => $client->clientPC,
                'description' => $client->clientDescription,
                'status' => $client->clientState,
                'location_id' => ($client->locationId > 0) ? $client->locationId : 1,
                'type_id' => ($client->typeId > 0) ? $client->typeId : 1,
                'visible' => ($client->noVisible === 0) ? 'yes' : 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Managers migration.
        $managers = DB::connection('agoraportal')
            ->table('agoraportal_client_managers')
            ->select(['managerId', 'clientCode', 'managerUName'])
            ->get();

        foreach ($managers as $manager) {
            $clientId = DB::table('clients')
                ->where('code', $manager->clientCode)
                ->value('id');

            $userId = DB::table('users')
                ->where('name', $manager->managerUName)
                ->value('id');

            DB::table('managers')->insert([
                'id' => $manager->managerId,
                'client_id' => $clientId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Instances migration.
        $instances = DB::connection('agoraportal')
            ->table('agoraportal_client_services')
            ->select(['clientServiceId', 'serviceId', 'clientId', 'description', 'state', 'activedId', 'contactName', 'contactProfile',
                'timeCreated', 'annotations', 'diskSpace', 'timeEdited', 'timeRequested', 'diskConsume', 'dbHost'])
            ->get();

        $statusEquivalent = [
            '0' => Instance::STATUS_PENDING,
            '1' => Instance::STATUS_ACTIVE,
            '-2' => Instance::STATUS_DENIED,
            '-3' => Instance::STATUS_WITHDRAWN,
            '-4' => Instance::STATUS_INACTIVE,
            '-5' => Instance::STATUS_BLOCKED,
            '-6' => Instance::STATUS_BLOCKED,
            '-7' => Instance::STATUS_BLOCKED,
        ];

        foreach ($instances as $instance) {
            DB::table('instances')->insert([
                'id' => $instance->clientServiceId,
                'client_id' => $instance->clientId,
                'service_id' => $instance->serviceId,
                'status' => $statusEquivalent[$instance->state],
                'db_id' => $instance->activedId,
                'db_host' => $instance->dbHost,
                'quota' => $instance->diskSpace * 1024 * 1024,
                'used_quota' => $instance->diskConsume * 1024,
                'model_type_id' => 1,
                'contact_name' => $instance->contactName,
                'contact_profile' => $instance->contactProfile,
                'observations' => $instance->observations,
                'annotations' => $instance->annotations,
                'requested_at' => $instance->timeRequested,
                'created_at' => $instance->timeCreated,
                'updated_at' => $instance->timeEdited,
            ]);
        }

    }

}
