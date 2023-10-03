@can('Administrate site')
<nav class="navbar-default admin-menu" role="navigation">
    <ul class="nav navbar-nav primary">
        <li>
            <a href="{{ route('instances.index') }}" @if (str_contains(request()->url(), 'instance')) class="selected" @endif>
                {{ __('service.instances') }}
            </a>
        </li>
        <li>
            <a href="{{ route('clients.index') }}" @if (str_contains(request()->url(), 'clients')) class="selected" @endif>
                {{ __('client.clients') }}
            </a>
        </li>
        <li>
            <a href="{{ route('requests.index') }}" @if (str_contains(request()->url(), 'requests')) class="selected" @endif>
                {{ __('request.requests') }}
            </a>
        </li>
        <li>
            <a href="{{ route('services.index') }}" @if (str_contains(request()->url(), 'services')) class="selected" @endif>
                {{ __('service.services') }}
            </a>
        </li>
        <li>
            <a href="{{ route('home') }}" @if (str_contains(request()->url(), 'stats')) class="selected" @endif>
                {{ __('stats.stats') }}
            </a>
        </li>
        <li>
            <a href="{{ route('home') }}" @if (str_contains(request()->url(), 'files')) class="selected" @endif>
                {{ __('common.files') }}
            </a>
        </li>
        <li>
            <a href="{{ route('batch') }}" @if (str_contains(request()->url(), 'batch')) class="selected" @endif>
                {{ __('batch.batch_actions') }}
            </a>
        </li>
        <li>
            <a href="{{ route('config.edit') }}" @if (str_contains(request()->url(), 'config')) class="selected" @endif>
                {{ __('config.configuration') }}
            </a>
        </li>
        <li>
            <a href="{{ route('home') }}" @if (str_contains(request()->url(), 'usuaris')) class="selected" @endif>
                {{ __('user.users') }}
            </a>
        </li>
    </ul>

    @if (str_contains(request()->url(), 'batch'))
        <ul class="nav navbar-nav secondary">
            <li>
                <a href="{{ route('batch.query') }}" @if (str_contains(request()->url(), 'query')) class="selected" @endif>
                    {{ __('batch.queries') }}
                </a>
            </li>
            <li>
                <a href="{{ route('operation') }}" @if (str_contains(request()->url(), 'operation')) class="selected" @endif>
                    {{ __('batch.operations') }}
                </a>
            </li>
            <li>
                <a href="{{ route('queue') }}" @if (str_contains(request()->url(), 'queue')) class="selected" @endif>
                    {{ __('batch.queues') }}
                </a>
            </li>
            <li>
                <a href="{{ route('batch.instance.create') }}" @if (str_contains(request()->url(), 'instance')) class="selected" @endif>
                    {{ __('batch.batch_creation') }}
                </a>
            </li>
        </ul>
    @endif

    @if (str_contains(request()->url(), 'config'))
        <ul class="nav navbar-nav secondary">
            <li>
                <a href="{{ route('config.edit') }}" @if (str_ends_with(request()->url(), 'config')) class="selected" @endif>
                    {{ __('common.params') }}
                </a>
            </li>
            <li>
                <a href="{{ route('requests.index') }}" @if (str_contains(request()->url(), 'requests')) class="selected" @endif>
                    {{ __('request.requests') }}
                </a>
            </li>
            <li>
                <a href="{{ route('models.index') }}" @if (str_contains(request()->url(), 'models')) class="selected" @endif>
                    {{ __('model.models') }}
                </a>
            </li>
            <li>
                <a href="{{ route('request-types.index') }}" @if (str_contains(request()->url(), 'request-types')) class="selected" @endif>
                    {{ __('request.request_types') }}
                </a>
            </li>
            <li>
                <a href="{{ route('locations.index') }}" @if (str_contains(request()->url(), 'locations')) class="selected" @endif>
                    {{ __('config.locations') }}
                </a>
            </li>
            <li>
                <a href="{{ route('client-types.index') }}" @if (str_contains(request()->url(), 'client-types')) class="selected" @endif>
                    {{ __('client.client_types') }}
                </a>
            </li>
        </ul>
    @endif
</nav>
@endcan
