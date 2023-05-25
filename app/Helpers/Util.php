<?php

namespace App\Helpers;

use App\Models\Client;
use Illuminate\Http\Request;

class Util {

    /**
     * Get the client from the URL if it is available and valid.
     *
     * @param Request $request
     * @return array
     */
    public static function get_client_from_url(Request $request): array {
        if ($request->has('code')) {
            $client_code = $request->get('code');

            if (!(new self)->isValidCode($client_code)) {
                return [];
            }

            $client = Client::where('code', $client_code)->first();

            if (!is_null($client)) {
                $current_client = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'code' => $client->code,
                    'dns' => $client->dns,
                ];

                $request->session()->put('current_client', $current_client);
            }
        }

        return $current_client ?? [];
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
        return !(strlen($code) === 8 || !preg_match("/^(a|b|c|e)\d{7}$/", $code));
    }

    /**
     * Returns if the current URL is in the selected domain or not.
     *
     * @param string $domain Domain to compare
     * @return bool
     */
    public function is_in_domain(string $domain): bool {
        $length = strlen($_SERVER['HTTP_HOST']);
        $start = $length * -1; // Negative

        return substr($domain, $start) === $_SERVER['HTTP_HOST'];
    }

    /**
     * Convert a code starting with a letter to a code starting with a number
     * and viceversa.
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
}
