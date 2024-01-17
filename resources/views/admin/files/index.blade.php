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
                    <th colspan="2">{{ __('common.actions') }}</th>
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
                                    <a href="{{ route('files.download', base64_encode($directory . $file['name'])) }}" title="{{ __('file.download') }}: {{ $directory . $file['name'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 512 512">
                                            <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                            <path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if (is_file($directory . $file['name']))
                                    <form action="{{ route('files.destroy', [base64_encode($directory), base64_encode($file['name'])]) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este archivo?');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="18" viewBox="0 0 448 512">
                                                <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                <path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0H284.2c12.1 0 23.2 6.8 28.6 17.7L320 32h96c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 96 0 81.7 0 64S14.3 32 32 32h96l7.2-14.3zM32 128H416V448c0 35.3-28.7 64-64 64H96c-35.3 0-64-28.7-64-64V128zm96 64c-8.8 0-16 7.2-16 16V432c0 8.8 7.2 16 16 16s16-7.2 16-16V208c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16V432c0 8.8 7.2 16 16 16s16-7.2 16-16V208c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16V432c0 8.8 7.2 16 16 16s16-7.2 16-16V208c0-8.8-7.2-16-16-16z"/>
                                            </svg>
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
