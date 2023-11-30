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
                        <td>{{ $file['size'] }}</td>
                        <td>{{ $file['updated_at'] }}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>


    </div>
@endsection
