@php use App\Models\Log; @endphp

@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($current_client['name']))
            <h3>{{ __('manager.log_list', ['name' => $current_client['name']]) }}</h3>
        @endif

        @include('components.messages')

        <div class="pull-right">
            {{ $log->links('pagination::bootstrap-4') }}
        </div>

        @if (!empty($log))
            <table class="table table-responsive">
                <thead>
                <tr>
                    <th>{{ __('standardlog.action_type') }}</th>
                    <th>{{ __('standardlog.action_description') }}</th>
                    <th>{{ __('user.user') }}</th>
                    <th>{{ __('common.date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($log as $log_item)
                    <tr>
                        <td>
                            @if ($log_item['action_type'] === Log::ACTION_TYPE_ADD)
                                <span class="btn btn-success glyphicon glyphicon-plus"
                                      aria-hidden="true"
                                      aria-label="{{ __('standardlog.action_add') }}"
                                      title="{{ __('standardlog.action_add') }}">
                                </span>
                            @elseif ($log_item['action_type'] === Log::ACTION_TYPE_EDIT)
                                <span class="btn btn-info glyphicon glyphicon-flag"
                                      aria-hidden="true"
                                      aria-label="{{ __('standardlog.action_edit') }}"
                                      title="{{ __('standardlog.action_edit') }}">
                                </span>
                            @elseif ($log_item['action_type'] === Log::ACTION_TYPE_DELETE)
                                <span class="btn btn-warning glyphicon glyphicon-trash"
                                      aria-hidden="true"
                                      aria-label="{{ __('standardlog.action_delete') }}"
                                      title="{{ __('standardlog.action_delete') }}">
                                </span>
                            @elseif ($log_item['action_type'] === Log::ACTION_TYPE_ERROR)
                                <span class="btn btn-danger glyphicon glyphicon-remove"
                                      aria-hidden="true"
                                      aria-label="{{ __('standardlog.action_error') }}"
                                      title="{{ __('standardlog.action_error') }}">
                                </span>
                            @elseif ($log_item['action_type'] === Log::ACTION_TYPE_ADMIN)
                                <span class="btn btn-primary glyphicon glyphicon-user"
                                      aria-hidden="true"
                                      aria-label="{{ __('standardlog.action_admin') }}"
                                      title="{{ __('standardlog.action_admin') }}">
                                </span>
                            @endif
                        </td>
                        <td>{{ $log_item['action_description'] }}</td>
                        <td>{{ $log_item->user->name }}</td>
                        <td>{{ $log_item['created_at'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endif
    </div>
@endsection
