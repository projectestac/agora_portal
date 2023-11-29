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
                @if ($parentDirectory)
                    <tr>
                        <td>
                            <a href="{{ route('files.index', $relativePath.'/..') }}">..</a>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
                @foreach ($files as $file)
                    <tr>
                        <td>
                            <a href="{{ route('files.index', $file['name']) }}">{{ $file['name'] }}</a>
                        </td>
                        <td>{{ $file['size'] }}</td>
                        <td>{{ $file['updated_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


    </div>
@endsection
