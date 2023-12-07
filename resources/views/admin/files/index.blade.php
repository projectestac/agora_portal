@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content files">
        <h3>Llista de fitxers</h3>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>NAME</th>
                    <th>SIZE</th>
                    <th>UPDATED_AT</th>
                    <th>DOWNLOAD</th>
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
                                        <a
                                            href="{{ route('files.index', base64_encode($path . '/' . $file['name'])) }}">{{ $file['name'] }}</a>
                                    @endif
                                @else
                                    {{ $file['name'] }}
                                @endif
                            </td>
                            <td>{{ \App\Helpers\Util::formatBytes($file['size']) }}</td>
                            <td>{{ $file['updated_at'] }}</td>
                            <td>
                                @if (is_file($directory . $file['name']))
                                    <a href="{{ route('files.download', base64_encode($directory . $file['name'])) }}" title="{{ $directory . $file['name'] }}">{{ __('file.download') }}</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- Formulario para subir un fichero -->
        @include('admin.files.uploadFile')
    </div>
@endsection
