<div style="padding:10px; background-color:#ffffff">
    <h3>Instàncies amb ocupació elevada</h3>

    @if(!empty($warning) && is_array($warning))
        <h4 style="color:#856404; background-color:#fff3cd; border-color:#ffeeba; display:inline-block; padding:3px;">
            A punt d'exhaurir la quota
        </h4>

        <table class="table table-striped" style="border:1px solid #856404; border-collapse:collapse; width:100%;">
            <thead>
            <tr>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">Servei</th>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">Codi</th>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">Ocupació</th>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">URL</th>
            </tr>
            </thead>
            <tbody>
            @foreach($warning as $instance)
                <tr>
                    <td style="border:1px solid #ccc; padding:5px; text-align:left;">{{ $instance['serviceName'] }}</td>
                    <td style="border:1px solid #ccc; padding:5px; text-align:left;">{{ $instance['code'] }}</td>
                    <td style="color:#856404; background-color:#fff3cd; border-color:#ffeeba;">
                        {{ $instance['percentage']}}
                        ({{ \App\Helpers\Util::formatBytes($instance['quotaUsed'])}} / {{ \App\Helpers\Util::formatBytes($instance['quota'])}})
                    </td>
                    <td style="border:1px solid #ccc; padding:5px; text-align:left;">
                        <a href="{{ $instance['url'] }}">{{ $instance['url'] }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($danger) && is_array($danger))
        <h4 style="color:#721c24; background-color:#f8d7da; border-color:#f5c6cb; display:inline-block; padding:3px;">
            Quota exhaurida
        </h4>

        <table class="table table-striped quota-table" style="border:1px solid #721c24; border-collapse:collapse; width:100%;">
            <thead>
            <tr>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">Servei</th>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">Codi</th>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">Ocupació</th>
                <th style="border:1px solid #ccc; padding:5px; text-align:left;">URL</th>
            </tr>
            </thead>
            <tbody>
            @foreach($danger as $instance)
                <tr>
                    <td style="border:1px solid #ccc; padding:5px; text-align:left;">{{ $instance['serviceName'] }}</td>
                    <td style="border:1px solid #ccc; padding:5px; text-align:left;">{{ $instance['code'] }}</td>
                    <td style="color:#721c24; background-color:#f8d7da; border-color:#f5c6cb;">
                        {{ $instance['percentage']}}
                        ({{ \App\Helpers\Util::formatBytes($instance['quotaUsed'])}}/{{ \App\Helpers\Util::formatBytes($instance['quota'])}})
                    </td>
                    <td style="border:1px solid #ccc; padding:5px; text-align:left;">
                        <a href="{{ $instance['url'] }}">{{ $instance['url'] }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
