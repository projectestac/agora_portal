@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content files">
        <h3>{{ __('file.file_list') }}</h3>

        @include('components.messages')

        <!-- Upload form -->
        @include('admin.files.uploadFile')

        <table class="table table-striped">
            <thead>
            <tr>
                <th>{{ __('common.name') }}</th>
                <th>{{ __('file.size') }}</th>
                <th>{{ __('common.updated_at') }}</th>
                <th>{{ __('common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @if (!empty($parentDirectory))
                <tr>
                    <td>
                        <a href="{{ route('files.index', base64_encode($parentDirectory)) }}">..</a>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
            @if (!empty($files))
                @foreach ($files as $file)
                    <tr>
                        <td>
                            @if (is_dir($directory . $file['name']))
                                @if ($file['name'] !== $baseDirectory)
                                    <a href="{{ route('files.index', base64_encode($path . '/' . $file['name'])) }}">{{ $file['name'] }}</a>
                                @endif
                            @else
                                {{ $file['name'] }}
                            @endif
                        </td>
                        <td>
                            @if (is_file($directory . $file['name']))
                                {{ \App\Helpers\Util::formatBytes($file['size']) }}
                            @endif
                        </td>
                        <td>{{ $file['updated_at'] }}</td>
                        <td>
                            @if (is_file($directory . $file['name']))
                                <a href="{{ route('files.download', base64_encode($directory . $file['name'])) }}"
                                   title="{{ __('file.download') }}: {{ $directory . $file['name'] }}"
                                   class="btn btn-primary">
                                    <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
                                </a>
                            @endif
                            @if (is_file($directory . $file['name']))
                                <form action="{{ route('files.destroy', [base64_encode($directory), base64_encode($file['name'])]) }}" method="POST"
                                      onsubmit="return confirm('{{ __('file.confirm_deletion', ['filename' => $file['name']]) }}');"
                                      style="display:inline;">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
@endsection
