<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateConfigRequest;
use App\Models\Config;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ConfigController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function edit(): View {
        $configuration = Config::select('name', 'value')
            ->orderBy('id')
            ->get()
            ->toArray();

        $config = [];
        foreach ($configuration as $item) {
            $config[$item['name']] = $item['value'];
        }

        return view('admin.config.edit')->with('config', $config);
    }

    public function update(UpdateConfigRequest $request): RedirectResponse {

        $params = [
            'notify_address_quota' => $request->input('notify_address_quota'),
            'notify_address_request' => $request->input('notify_address_request'),
            'notify_address_user_cco' => $request->input('notify_address_user_cco'),
            'quota_usage_to_request' => $request->input('quota_usage_to_request'),
            'quota_free_to_request' => $request->input('quota_free_to_request'),
            'quota_usage_to_notify' => $request->input('quota_usage_to_notify'),
            'quota_free_to_notify' => $request->input('quota_free_to_notify'),
            'xtecadmin_hash' => $request->input('xtecadmin_hash'),
            'max_file_size_for_large_upload' => $request->input('max_file_size_for_large_upload'),
            'nodes_create_db' => ($request->input('nodes_create_db') === 'on') ? 1 : 0,
            'min_db_id' => $request->input('min_db_id'),
            'file_extensions_allowed' => $request->input('file_extensions'),
            'send_quotas_email' => ($request->input('send_quotas_email') === 'on') ? 1 : 0,
        ];

        // Encrypt password if needed.
        $params['xtecadmin_hash'] = (strlen($params['xtecadmin_hash']) === 60) ? $params['xtecadmin_hash'] : password_hash($params['xtecadmin_hash'], PASSWORD_BCRYPT);

        // Divide quota usage values by 100.
        $params['quota_usage_to_request'] /= 100;
        $params['quota_usage_to_notify'] /= 100;

        foreach ($params as $name => $value) {
            Config::where('name', $name)->update(['value' => $value]);
        }

        return redirect()->route('config.edit')->with('success', __('config.config_updated'));
    }

}
