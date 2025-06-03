@canany(['Administrate site', 'Manage own managers', 'Manage clients'])

    <nav class="navbar-default admin-menu" role="navigation">
        <ul class="nav navbar-nav primary">
            <li>
                <a href="{{ route('myagora.instances') }}" @if (str_contains(request()->url(), 'instance')) class="selected" @endif>
                    {{ __('service.services') }}
                </a>
            </li>
            @canany(['Administrate site', 'Manage clients'])
                <li>
                    <a href="{{ route('myagora.files') }}" @if (str_contains(request()->url(), 'files')) class="selected" @endif>
                        {{ __('file.files') }}
                    </a>
                </li>
            @endcanany
            <li>
                <a href="{{ route('myagora.requests') }}" @if (str_contains(request()->url(), 'requests')) class="selected" @endif>
                    {{ __('request.requests') }}
                </a>
            </li>
            <li>
                <a href="{{ route('myagora.managers') }}" @if (str_contains(request()->url(), 'managers')) class="selected" @endif>
                    {{ __('manager.managers') }}
                </a>
            </li>
            <li>
                <a href="{{ route('myagora.logs') }}" @if (str_contains(request()->url(), 'logs')) class="selected" @endif>
                    {{ __('standardlog.title') }}
                </a>
            </li>
        </ul>

        @if (isset($clients) && $clients->count() > 1)
            <div class="client-switcher">
                <form method="POST" action="{{ route('clients.switch') }}" class="form-inline mb-3">
                    @csrf
                    <select name="client_id" id="clientSwitcher" class="form-control" onchange="this.form.submit()">
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ isset($currentClient['id']) && $currentClient['id'] === $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

    </nav>
@endcan
