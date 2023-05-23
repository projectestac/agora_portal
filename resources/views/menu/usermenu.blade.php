<nav class="navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex6-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <span class="navbar-brand visible-xs"></span>
    </div>
    <div class="collapse navbar-collapse navbar-ex6-collapse">
        @if(auth()->check())
            <p class="navbar-text">[ {{ auth()->user()->name }} ]</p>
        @endif

        <ul class="nav navbar-nav">
            @guest()
                <li><a href="{{ route('login') }}">{{ __('common.login') }}</a></li>
            @endguest

            @can('Administrate site')
                <li><a href="{{ route('instances.index') }}">{{ __('common.admin') }}</a></li>
            @endcan

            <li><a href="https://educaciodigital.cat/moodle/moodle/mod/page/view.php?id=1781">{{ __('common.faq') }}</a></li>

            @auth()
                <li><a href="{{ route('myagora') }}">{{ __('common.my_agora') }}</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                         onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('common.logout') }}
                        </x-dropdown-link>
                    </form>
                </li>
            @endauth
        </ul>
    </div>
</nav>
