@php
    use App\Models\Request;
    use Carbon\Carbon;
@endphp

@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('request.request_client_list', ['name' => $currentClient['name']]) }}</h3>
        @endif

        @include('components.messages')

        @if(count($availableRequests) > 0)
            <div class="panel panel-default">
                <div class="panel-heading row-fluid clearfix">
                    {{ __('request.available_requests') }}
                </div>
                <div class="panel-body">
                    <form action="{{ route('requests.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="request-select-request">{{ __('request.request') }}</label>
                            <select class="form-control" id="request-select-request" name="request-select-request">
                                <option value="0">{{ __('request.chose_request') }}</option>
                                @foreach ($availableRequests as $serviceName => $availableRequest)
                                    <optgroup label="{{ $serviceName }}">
                                    @foreach ($availableRequest as $request)
                                        <option value="{{ $request->service_id }}:{{ $request->id }}">{{ $request->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div id="request-user-messages"></div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    $('#request-select-request').change(function () {
                        $.ajax({
                            url: '{{ url('/myagora/request/details') }}',
                            method: 'GET',
                            data: {
                                option: $(this).val()
                            },
                            success: function (response) {
                                $('#request-user-messages').html(response.html);
                            },
                            error: function (xhr, status, error) {
                                console.log('Error on AJAX call:', error);
                            }
                        });
                    });
                });
            </script>

        @endif

        <div class="pull-right">
            {{ $requests->links('pagination::bootstrap-4') }}
        </div>

        @if (!empty($requests))
            <table class="table table-responsive">
                <thead>
                <tr>
                    <th>{{ __('request.requester') }}</th>
                    <th>{{ __('request.request_date') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('service.service') }}</th>
                    <th>{{ __('request.admin_reply') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests as $request)
                    @php
                        $created_at = Carbon::parse($request['created_at']);
                        $updated_at = Carbon::parse($request['updated_at']);
                    @endphp

                    <tr>
                        <td>{{ $request->user->name}}</td>
                        <td>{{ $created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($created_at->format('d/m/Y H:i:s') !== $updated_at->format('d/m/Y H:i:s'))
                                {{ $updated_at->format('d/m/Y H:i') }}
                            @endif
                        </td>
                        <td>
                            @if ($request['status'] === Request::STATUS_PENDING)
                                <span class="btn btn-warning glyphicon glyphicon-time"
                                      aria-hidden="true"
                                      aria-label="{{ __('request.status_pending') }}"
                                      title="{{ __('request.status_pending') }}">
                                </span>
                            @elseif ($request['status'] === Request::STATUS_UNDER_STUDY)
                                <span class="btn btn-warning glyphicon glyphicon-time"
                                      aria-hidden="true"
                                      aria-label="{{ __('request.status_under_study') }}"
                                      title="{{ __('request.status_under_study') }}">
                                </span>
                            @elseif ($request['status'] === Request::STATUS_SOLVED)
                                <span class="btn btn-success glyphicon glyphicon-ok"
                                      aria-hidden="true"
                                      aria-label="{{ __('request.status_solved') }}"
                                      title="{{ __('request.status_solved') }}">
                                </span>
                            @elseif ($request['status'] === Request::STATUS_DENIED)
                                <span class="btn btn-danger glyphicon glyphicon-ban-circle"
                                      aria-hidden="true"
                                      aria-label="{{ __('request.status_denied') }}"
                                      title="{{ __('request.status_denied') }}">
                                </span>
                            @endif
                        </td>
                        <td>{{ $request->requestType->name }}</td>
                        <td>{{ $request->service->name }}</td>
                        <td>{{ $request['admin_comment'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

    </div>
@endsection
