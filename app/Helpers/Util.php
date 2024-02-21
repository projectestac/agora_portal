<?php

namespace App\Helpers;

use App\Models\Client;
use App\Models\Config as ConfigModel;
use App\Models\Instance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class Util {

    public const SERVEIEDUCATIU_TYPE_ID = 5;
    public const EOI_TYPE_ID = 6;
    public const PROJECTES_TYPE_ID = 12;

    /**
     * Get the client from the URL if it is available and valid.
     *
     * @param Request $request
     * @return array
     */
    public static function getClientFromUrl(Request $request): array {
        if ($request->has('code')) {
            $clientCode = $request->get('code');

            if (!(new self)->isValidCode($clientCode)) {
                return [];
            }

            $client = Client::where('code', $clientCode)->first();

            if (!is_null($client)) {
                $currentClient = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'code' => $client->code,
                    'dns' => $client->dns,
                ];

                $request->session()->put('currentClient', $currentClient);
            }
        }

        return $currentClient ?? [];
    }

    /**
     * Check if the specified DNS is valid to avoid security problems.
     *
     * @param string $dns
     * @return boolean True if specified DNS is correct, false otherwise.
     */
    public function isValidDNS(string $dns): bool {
        return !(strlen($dns) > 30 || !preg_match("/^[a-z0-9-_]+$/", $dns));
    }

    /**
     * Check if the specified code is valid to avoid security problems. The code must be
     * eight characters long and start with a letter a,b,c or e. The seven remaining characters
     * must be numbers.
     *
     * @param string $code
     * @return boolean True if specified code is correct, false otherwise.
     */
    public function isValidCode(string $code): bool {
        return (strlen($code) === 8 && preg_match("/^([abce])\d{7}$/", $code));
    }

    /**
     * Returns if the current URL is in the selected domain or not.
     *
     * @param string $domain Domain to compare
     * @return bool
     */
    public function isInDomain(string $domain): bool {
        $length = strlen($_SERVER['HTTP_HOST']);
        $start = $length * -1; // Negative

        return substr($domain, $start) === $_SERVER['HTTP_HOST'];
    }

    /**
     * Convert a code starting with a letter to a code starting with a number and viceversa.
     *
     * @param string $clientCode
     * @param string $type
     *
     * @return string Client code transformed
     */
    public function transformClientCode(string $clientCode, string $type = 'letter2num'): string {
        if ($type === 'letter2num') {
            $pattern = '/^[abce]\d{7}$/'; // Matches a1234567
            if (preg_match($pattern, $clientCode)) {
                // Convert a client code beginning with a letter to a client code beginning with a number.
                $search = ['a', 'b', 'c', 'e'];
                $replace = ['0', '1', '2', '4'];
                $clientCode = str_replace($search, $replace, $clientCode);
            }
        } elseif ($type === 'num2letter') {
            $pattern = '/^\d{8}$/'; // Matches 01234567
            if (preg_match($pattern, $clientCode)) {
                // Convert first number into a letter
                switch ($clientCode[0]) {
                    case '0':
                        $clientCode[0] = 'a';
                        break;
                    case '1':
                        $clientCode[0] = 'b';
                        break;
                    case '2':
                        $clientCode[0] = 'c';
                        break;
                    case '4':
                        $clientCode[0] = 'e';
                        break;
                }
            }
        }

        return $clientCode;
    }

    /**
     * Convert a file size string to bytes.
     *
     * @param string $sizeString String representing the file size, e.g., "2M" or "512K".
     * @return int Size of the file in bytes.
     */
    public static function convertToBytes(string $sizeString): int {
        // Convert the string to uppercase to handle file size units in either case.
        $sizeString = strtoupper($sizeString);

        // Define units and their corresponding exponents.
        $units = ['B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5, 'E' => 6];

        // Use a regular expression to separate the numeric part and the unit.
        preg_match('/^(\d+)([BKMGTEP]?)$/', $sizeString, $matches);

        // Get the size and unit.
        $size = (int)$matches[1];
        $unit = $matches[2] ?? 'B';

        // Calculate and return the size in bytes.
        return $size * (1024 ** $units[$unit]);
    }

    /**
     * Function to convert a number representing a disk size in bytes to a human-readable format.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Function to convert a number representing a disk size in bytes to GB (1024 * 1024 * 1024 bytes).
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatGb(int $bytes, int $precision = 2): string {
        return round($bytes / 1024 / 1024 / 1024, $precision);
    }

    public static function getFormattedDiskUsage(int $used, int $total): string {
        return self::formatBytes($used) . ' / ' . self::formatBytes($total) . ' (' . round($used / $total * 100) . '%)';
    }

    public static function getColoredFormattedDiskUsage(int $used, int $total): string {
        $quota_usage_to_request = self::getConfigParam('quota_usage_to_request');
        $usage_ratio = $used / $total;

        if ($usage_ratio < $quota_usage_to_request) {
            $color_class = 'alert alert-success';
        } elseif ($usage_ratio < 1) {
            $color_class = 'alert alert-warning';
        } else {
            $color_class = 'alert alert-danger';
        }

        return '<span class="' . $color_class . '" style="display:inline-block; width:100%; text-align:center; height:75px; margin-bottom:0;">' . self::getFormattedDiskUsage($used, $total) . '</span>';
    }

    public static function getAgoraVar(string $varName = ''): string {
        if (empty($varName)) {
            return '';
        }

        return match ($varName) {
            'portaldata' => Config::get('app.agora.server.root') .
                Config::get('app.agora.admin.datadir'),
            'nodesdata' => Config::get('app.agora.server.root') .
                Config::get('app.agora.nodes.datadir'),
            'moodledata' => Config::get('app.agora.server.root') .
                Config::get('app.agora.moodle2.datadir'),
            'moodle_quotas_file' => Config::get('app.agora.server.root') .
                Config::get('app.agora.moodle2.datadir') .
                Config::get('app.agora.moodle2.diskusagefile'),
            'nodes_quotas_file' => Config::get('app.agora.server.root') .
                Config::get('app.agora.nodes.datadir') .
                Config::get('app.agora.nodes.diskusagefile'),
            'moodle_user_prefix' => Config::get('app.agora.moodle2.userprefix'),
            'nodes_user_prefix' => Config::get('app.agora.nodes.userprefix'),
            'nodes_domain' => Config::get('app.agora.server.nodes'),
            'moodle_domain' => Config::get('app.agora.server.server'),
            'se_domain' => Config::get('app.agora.server.se-url'),
            'projectes_domain' => Config::get('app.agora.server.projectes'),
            'eoi_domain' => Config::get('app.agora.server.eoi'),
            default => '',
        };
    }

    public static function getFiles(string $dir = ''): array {

        if (empty($dir)) {
            return [];
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            $result[] = [
                'name' => $file,
                'size' => filesize($path),
                'updated_at' => date('d/m/Y H:i', filemtime($path)),
            ];
        }

        return $result ?? [];

    }

    public static function createRandomPass() {

        // Chars allowed in password
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz023456789";

        // Sets the seed for rand function
        srand((float)microtime() * 1000000);

        for ($i = 0, $pass = ''; $i < 8; $i++) {
            $num = rand() % strlen($chars);
            $pass = $pass . substr($chars, $num, 1);
        }

        return $pass;
    }

    /**
     * Retrieve the value of a configuration parameter from table "configs".
     *
     * @param string $name
     * @return string
     */
    public static function getConfigParam(string $name): string {
        $param = ConfigModel::select('value')
            ->where('name', $name)
            ->first();

        return $param->value ?? '';
    }

    public static function getInstanceUrl(Instance $instance): string {

        if ($instance->service->name === 'Moodle') {
            if ($instance->client->type_id === self::EOI_TYPE_ID) {
                return Config::get('app.agora.server.eoi') . '/' . $instance->client->dns . '/moodle/';
            }
            return Config::get('app.agora.server.server') . '/' . $instance->client->dns . '/moodle/';
        }

        return match ($instance->client->type_id) {
            self::SERVEIEDUCATIU_TYPE_ID => Config::get('app.agora.server.se-url') . '/' . $instance->client->dns . '/',
            self::EOI_TYPE_ID => Config::get('app.agora.server.eoi') . '/' . $instance->client->dns . '/',
            self::PROJECTES_TYPE_ID => Config::get('app.agora.server.projectes') . '/' . $instance->client->dns . '/',
            default => Config::get('app.agora.server.nodes') . '/' . $instance->client->dns . '/',
        };

    }

    public static function getManagersEmail(Client $client): array {

        $clientUser = User::where('name', $client->code)->first();
        $emails = (!empty($clientUser)) ? [$clientUser->email] : [$client->code . '@xtec.cat'];
        $managers = $client->managers()->get()->toArray();

        foreach ($managers as $manager) {
            $user = User::where('id', $manager['user_id'])->get()->toArray()[0];
            $emails[] = $user['email'];
        }

        return $emails;
    }

    public static function isMoodleInstanceActive(Client $client): bool {
        $client->instances()
            ->join('services', 'instances.service_id', '=', 'services.id')
            ->where('services.name', 'Moodle')
            ->where('instances.status', 'active')
            ->first();

        return !empty($client->instances);
    }

    /**
     * Get the string with School Information from Web Service. In case of error, return information about the error.
     * Possible errors:
     *  1. No data received.
     *  2. Client is not registered in master clients table.
     *  3. Client has no "nom propi".
     *
     * Test strings (values received from the Web Service):
     *  - Success: 'a8000001$$nompropi$$Nom del Centre$$c. Carrer, 18-24$$Valldevent$$00000'
     *  - Error #1: ''
     *  - Error #2: 'ERROR'
     *  - Error #3: 'a8000001$$0$$Nom del Centre$$c. Carrer, 18-24$$Valldevent$$00000'
     *
     * @param string $uname Codi de centre
     * @global array $agora
     * @return array ['error' => 0|1, 'message' => string]
     * @author Toni Ginard
     */
    public function getSchoolFromWS(string $uname): array {
        global $agora;

        // Build the URL.
        $codeNumber = (new self)->transformClientCode($uname, 'letter2num');
        $url = $agora['server']['school_information'] . $codeNumber;

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $buffer = curl_exec($handle);
        curl_close($handle);

        // $buffer = 'a8000001$$nompropi$$Nom del Centre$$c. Carrer, 18-24$$Valldevent$$00000';

        // Get school Data. Error #1: No data received.
        if (empty($buffer)) {
            return [
                'error' => 1,
                'message' => __('client.no_ws'),
            ];
        }

        $clientData = utf8_encode(trim($buffer));

        // Error #2: Client is not registered in master clients table.
        if (str_contains($clientData, 'ERROR')) {
            return [
                'error' => 1,
                'message' => __('client.no_client_in_ws', ['code' => $codeNumber]),
            ];
        }

        $clientDataArray = explode('$$', $clientData);

        // Error #3: Client has no "nom propi".
        if ($clientDataArray[1] === '0') {
            $results['error'] = 1;
            $results['message'] = __('client.client_has_no_nompropi');
        } else {
            // Success.
            $results['error'] = 0;
            $results['message'] = $clientData;
        }

        return $results;
    }

}
