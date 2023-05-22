
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
        <ul class="nav navbar-nav">
            <li><a href="{{ route('login') }}">Entra</a></li>
            <li><a href="#">PMF</a></li>
            <li><a href="#">El meu Àgora</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                                     onclick="event.preventDefault(); this.closest('form').submit();">
                        Surt
                    </x-dropdown-link>
                </form>

            </li>
        </ul>
    </div>
</nav>
