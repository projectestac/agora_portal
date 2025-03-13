@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content manager">
        <h3>{{ __('manager.add_manager') }}</h3>

        @include('components.messages')

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

        <form class="form-horizontal" action="{{ route('managers.store_new') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="username">{{ __('manager.user') }}</label>
                <div class="col-sm-8">
                    <input type="text" id="username" class="form-control" placeholder="{{ __('manager.type_user') }}">
                    <input type="hidden" name="username" id="username_hidden">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="client_id">{{ __('manager.client') }}</label>
                <div class="col-sm-8">
                    <input type="text" id="client_id" class="form-control" placeholder="{{ __('manager.type_client') }}">
                    <input type="hidden" name="client_id" id="client_id_hidden">
                </div>
            </div>

            <div class="form-group">
                <div class="text-center">
                    <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                </div>
            </div>

        </form>

        <script>
            $(document).ready(function () {
                var users = @json($users->pluck('name'));
                var clients = @json($clients->pluck('name', 'id'));

                $("#username").autocomplete({
                    source: users,
                    select: function (event, ui) {
                        $("#username_hidden").val(ui.item.value);
                    }
                });

                $("#client_id").autocomplete({
                    source: Object.values(clients),
                    select: function (event, ui) {
                        var selectedClientId = Object.keys(clients).find(key => clients[key] === ui.item.value);
                        $("#client_id_hidden").val(selectedClientId);
                    }
                });
            });
        </script>

        <style>
            .ui-autocomplete {
                max-height: 400px;
                overflow-y: auto;
                overflow-x: hidden;
            }
        </style>
    </div>

@endsection
