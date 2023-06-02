@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('manager.manager_list', ['name' => $currentClient['name']]) }}</h3>
        @endif

        @include('components.messages')

        @if (!empty($managers))
            <table class="table table-responsive">
                <thead>
                <tr>
                    <th>{{ __('user.user') }}</th>
                    <th>{{ __('user.email') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($managers as $manager)
                    <tr>
                        <td>{{ $manager->name }}</td>
                        <td>{{ $manager->email }}</td>
                        <td>
                            <form action="{{ route('managers.destroy', $manager->id) }}" method="POST" style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="{{ __('standardlog.delete', ['name' => $manager->name]) }}">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-warning">{{ __('manager.no_managers') }}</div>
        @endif

        @if(count($managers) < $max_managers)
            <div class="panel panel-default">
                <div class="panel-heading row-fluid clearfix">
                    {{ __('manager.add_manager') }}
                </div>
                <div class="panel-body">
                    <div class="alert alert-warning">{{ __('manager.notify_user') }}</div>
                    <form action="{{ route('managers.store') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <label for="username" class="col-xs-12 col-sm-4 text-right">
                                {{ __('manager.add_manager_text') }}
                            </label>
                            <div class="col-xs-12 col-sm-8">
                                <input type="text" class="form-control" id="username" name="username"/>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <button type="submit" class="btn btn-primary center-block">{{ __('common.add') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-warning">{{ __('manager.max_managers', ['number' => $max_managers]) }}</div>
        @endif
    </div>
@endsection
