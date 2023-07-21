<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Instance;
use App\Models\Query;
use App\Models\Request;
use App\Models\RequestType;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class MigrationSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        // Users migration.
        echo 'Users migration started...' . "\n";
        $users = DB::connection('agoraportal')
            ->table('users')
            ->select(['uid', 'uname', 'email', 'pass', 'user_regdate', 'lastlogin', 'approved_date'])
            ->get();

        foreach ($users as $user) {
            // MySQL doesn't accept '1970-01-01 00:00:00' as a valid date, so we need to change it to '1970-01-02 00:00:00'.
            $lastLogin = ($user->lastlogin === '1970-01-01 00:00:00' || $user->lastlogin === '0000-00-00 00:00:00') ? '1970-01-01 02:00:00' : $user->lastlogin;
            $userRegDate = ($user->user_regdate === '1970-01-01 00:00:00' || $user->user_regdate === '0000-00-00 00:00:00') ? '1970-01-01 02:00:00' : $user->user_regdate;

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

        // Groups to role migration.
        echo 'Groups migration started...' . "\n";
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
                'Developers' => 'developer',
            ];

            DB::table('roles')->insert([
                'id' => $group->gid,
                'name' => $replacement[$group->name],
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign permissions to roles.
        echo 'Adding permissions to roles...' . "\n";
        $permission = Permission::create(['name' => 'Administrate site']);
        $permission->assignRole(DB::table('roles')->where('name', 'admin')->first()->id);

        $permission = Permission::create(['name' => 'Manage own managers']);
        $permission->assignRole(DB::table('roles')->where('name', 'client')->first()->id);

        $permission = Permission::create(['name' => 'Manage clients']);
        $permission->assignRole(DB::table('roles')->where('name', 'manager')->first()->id);

        // Group membership migration.
        echo 'Membership migration started...' . "\n";
        $membership = DB::connection('agoraportal')
            ->table('group_membership')
            ->select(['gid', 'uid'])
            ->get();

        foreach ($membership as $member) {
            try {
                DB::table('model_has_roles')->insert([
                    'role_id' => $member->gid,
                    'model_type' => 'App\Models\User',
                    'model_id' => $member->uid,
                ]);
            } catch (\Exception $e) {
                // There can be duplicates in the group membership table, so we need to catch the exception and continue.
                echo $e->getMessage() . "\n";
                echo 'gid: ' . $member->gid . "\n";
                echo 'uid: ' . $member->uid . "\n";
                echo 'Execution continues...' . "\n";
            }
        }

        // Client types migration.
        echo 'Client types migration started...' . "\n";
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
        echo 'Services migration started...' . "\n";
        $services = DB::connection('agoraportal')
            ->table('agoraportal_services')
            ->select(['serviceId', 'serviceName', 'URL', 'description', 'defaultDiskSpace'])
            ->get();

        foreach ($services as $service) {
            // Remove old services
            if (in_array($service->serviceName, ['intranet', 'moodle', 'marsupial'])) {
                continue;
            }
            if ($service->serviceName === 'moodle2') {
                $service->serviceName = 'moodle';
            }
            DB::table('services')->insert([
                'id' => $service->serviceId,
                'name' => ucfirst($service->serviceName),
                'status' => 'active',
                'description' => $service->description,
                'slug' => $service->URL,
                'quota' => $service->defaultDiskSpace * 1024 * 1024,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Locations migration.
        echo 'Locations migration started...' . "\n";
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
        echo 'Model types migration started...' . "\n";
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
        echo 'Clients migration started...' . "\n";
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
                'status' => ($client->clientState) ? Client::STATUS_ACTIVE : Client::STATUS_INACTIVE,
                'location_id' => ($client->locationId > 0) ? $client->locationId : 1,
                'type_id' => ($client->typeId > 0) ? $client->typeId : 1,
                'visible' => ($client->noVisible === 0) ? 'yes' : 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Managers migration.
        echo 'Managers migration started...' . "\n";
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

            // If the client or the user doesn't exist, skip this manager.
            if (is_null($clientId) || is_null($userId)) {
                continue;
            }

            DB::table('managers')->insert([
                'id' => $manager->managerId,
                'client_id' => $clientId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Instances migration.
        echo 'Instances migration started...' . "\n";
        $instances = DB::connection('agoraportal')
            ->table('agoraportal_client_services')
            ->select(['clientServiceId', 'serviceId', 'clientId', 'description', 'state', 'activedId', 'contactName', 'contactProfile',
                'timeCreated', 'observations', 'annotations', 'diskSpace', 'timeEdited', 'timeRequested', 'diskConsume', 'dbHost'])
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
                // Set a time zone in UTC+3 to avoid conversion problems when the original timestamp is not set.
                'requested_at' => Carbon::createFromTimestampUTC($instance->timeRequested)->setTimezone('Europe/Istanbul')
                    ->toDateTimeString(),
                'created_at' => Carbon::createFromTimestampUTC($instance->timeCreated)->setTimezone('Europe/Istanbul')
                    ->toDateTimeString(),
                'updated_at' => Carbon::createFromTimestampUTC($instance->timeEdited)->setTimezone('Europe/Istanbul')
                    ->toDateTimeString(),
            ]);
        }

        // Queries migration.
        echo 'Queries migration started...' . "\n";
        $queries = DB::connection('agoraportal')
            ->table('agoraportal_mysql_comands')
            ->select(['comandId', 'serviceId', 'comand', 'description', 'type'])
            ->get();

        $typeEquivalent = [
            '0' => Query::TYPE_OTHER,
            '1' => Query::TYPE_SELECT,
            '2' => Query::TYPE_INSERT,
            '3' => Query::TYPE_UPDATE,
            '4' => Query::TYPE_DELETE,
            '5' => Query::TYPE_ALTER,
            '6' => Query::TYPE_DROP,
        ];

        foreach ($queries as $query) {
            DB::table('queries')->insert([
                'id' => $query->comandId,
                'service_id' => $query->serviceId,
                'query' => $query->comand,
                'description' => $query->description,
                'type' => $typeEquivalent[$query->type],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Request types migration.
        echo 'Request types migration started...' . "\n";
        $requestTypes = DB::connection('agoraportal')
            ->table('agoraportal_requestTypes')
            ->select(['requestTypeId', 'name', 'description', 'userCommentsText'])
            ->get();

        foreach ($requestTypes as $requestType) {
            DB::table('request_types')->insert([
                'id' => $requestType->requestTypeId,
                'name' => $requestType->name,
                'description' => $requestType->description,
                'prompt' => $requestType->userCommentsText,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Requests migration.
        echo 'Requests migration started...' . "\n";
        $requests = DB::connection('agoraportal')
            ->table('agoraportal_request')
            ->select(['requestId', 'requestTypeId', 'serviceId', 'clientId', 'userId', 'userComments', 'adminComments',
                'privateNotes', 'requestStateId', 'timeCreated', 'timeClosed'])
            ->get();

        $statusEquivalent = [
            '1' => Request::STATUS_PENDING,
            '2' => Request::STATUS_UNDER_STUDY,
            '3' => Request::STATUS_SOLVED,
            '4' => Request::STATUS_DENIED,
        ];

        foreach ($requests as $request) {
            // Skip the request if the integrity in the original database is broken.
            if (RequestType::where('id', $request->requestTypeId)->doesntExist() ||
                Service::where('id', $request->serviceId)->doesntExist() ||
                User::where('id', $request->userId)->doesntExist() ||
                Client::where('id', $request->clientId)->doesntExist()) {
                continue;
            }

            DB::table('requests')->insert([
                'id' => $request->requestId,
                'request_type_id' => $request->requestTypeId,
                'service_id' => $request->serviceId,
                'client_id' => $request->clientId,
                'user_id' => $request->userId,
                'status' => $statusEquivalent[$request->requestStateId],
                'user_comment' => $request->userComments,
                'admin_comment' => $request->adminComments,
                'private_note' => $request->privateNotes,
                'created_at' => Carbon::createFromTimestamp($request->timeCreated)->toDateTimeString(),
                // Set a time zone in UTC+3 to avoid conversion problems when the original timestamp is not set.
                'updated_at' => Carbon::createFromTimestampUTC($request->timeClosed)->setTimezone('Europe/Istanbul')
                    ->toDateTimeString(),
            ]);
        }

        // Request type services migration.
        echo 'Request type services migration started...' . "\n";
        $requestTypeServices = DB::connection('agoraportal')
            ->table('agoraportal_requestTypesServices')
            ->select(['requestTypeId', 'serviceId'])
            ->get();

        foreach ($requestTypeServices as $requestTypeService) {
            DB::table('request_type_service')->insert([
                'request_type_id' => $requestTypeService->requestTypeId,
                'service_id' => $requestTypeService->serviceId,
            ]);
        }

        // Logs migration.
        echo 'Logs migration started...' . "\n";
        $logs = DB::connection('agoraportal')
            ->table('agoraportal_logs')
            ->select(['logId', 'clientCode', 'uname', 'actionCode', 'action', 'time'])
            ->get();

        foreach ($logs as $log) {
            $clientId = DB::table('clients')
                ->where('code', $log->clientCode)
                ->value('id');

            $userId = DB::table('users')
                ->where('name', $log->uname)
                ->value('id');

            // Skip the log if the integrity in the original database is broken.
            if (is_null($clientId) || is_null($userId)) {
                continue;
            }

            DB::table('standard_logs')->insert([
                'id' => $log->logId,
                'client_id' => $clientId,
                'user_id' => $userId,
                'action_type' => $log->actionCode,
                'action_description' => $log->action,
                'created_at' => Carbon::createFromTimestamp($log->time)->toDateTimeString(),
                'updated_at' => Carbon::createFromTimestamp($log->time)->toDateTimeString(),
            ]);
        }

    }

}
