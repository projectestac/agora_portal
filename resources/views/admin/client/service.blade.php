<a href="{{ \App\Helpers\Util::getInstanceUrl($instance) }}"
   title="{{ $instance->service_name }} - {{ $instance->client_name }}">
    <img src="{{ secure_asset('images/' . mb_strtolower($instance->service_name . '.gif')) }}" alt="{{ $instance->service_name }}"
    >
</a>
