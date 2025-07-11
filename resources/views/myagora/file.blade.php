@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">

        @include('components.messages')

        @if(is_null($instanceId))

            <div class="alert alert-info">
                {{ __('file.no_instance') }}
            </div>

        @else

            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab1">{{ __('file.upload_a_file') }}</a></li>
                <li><a data-toggle="tab" href="#tab2">{{ __('file.file_list') }}</a></li>
            </ul>

            <div class="tab-content">
                <div id="tab1" class="tab-pane fade in active">
                    <h3>{{ __('file.send_files_to_moodle') }}</h3>
                    <meta name="csrf-token" content="{{ csrf_token() }}">

                    <div class="margin-top-2">
                        @include('myagora.components.quota-usage')
                    </div>

                    <div id="container" class="margin-top-2">
                        <button type="button" id="browse-button" class="btn btn-primary">{{ __('file.browse') }}...</button>
                        <button type="button" id="upload-files" class="btn btn-success">{{ __('common.send') }}</button>
                    </div>

                    <div id="file-list" class="margin-top-2"></div>
                    <div id="upload-status"></div>

                    <div class="alert alert-info margin-top-2">
                        {!! __('file.upload_info', ['maxFileSize' => $maxFileSize]) !!}
                    </div>
                    <script src="{{ route('home') }}/vendor/jildertmiedema/laravel-plupload/js/plupload.full.min.js"></script>

                    <script>
                        let uploader = new plupload.Uploader({
                            runtimes: 'html5,html4',
                            browse_button: 'browse-button',
                            container: document.getElementById('container'),
                            url: '{{ route('upload') }}',
                            multi_selection: false,
                            max_file_count: 1,
                            multiple_queues: false,
                            autostart: true,
                            flash_swf_url: false,
                            silverlight_xap_url: false,
                            chunk_size: '1mb',
                            filters: {
                                max_file_size: '{{ $maxFileSize }}mb',
                                mime_types: [
                                    {title: "{{ __('file.zip_files') }}", extensions: "{{ $extensions }}"}
                                ]
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },

                            init: {
                                PostInit: function () {
                                    document.getElementById('file-list').innerHTML = '';
                                    document.getElementById('upload-files').onclick = function () {
                                        uploader.start();
                                        return false;
                                    };
                                },

                                FilesAdded: function (up, files) {
                                    if (up.files.length > 1) {
                                        up.splice(0, up.files.length - 1);
                                    }
                                    plupload.each(files, function (file) {
                                        if (file.size < 10 * 1024 * 1024) {
                                            up.splice(0);
                                            document.getElementById('file-list').innerHTML = '';
                                            document.getElementById('upload-status').innerHTML = '<div class="alert alert-danger">{{ __('file.file_too_small') }}</div>';
                                        } else {
                                            document.getElementById('upload-status').innerHTML = '';
                                            document.getElementById('file-list').innerHTML = '<div id="' + file.id + '">' +
                                                '<strong>{{ __('file.selected_file') }}</strong>: ' + file.name + ' ' + '(' + plupload.formatSize(file.size) +
                                                ')</div>';
                                        }
                                    });
                                },

                                BeforeUpload: function (up, files) {
                                    document.getElementById('container').style.display = "none";
                                },

                                UploadProgress: function (up, file) {
                                    document.getElementById('upload-status').innerHTML = '<div class="progress" style="width:200px;">\
                                    <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="' + file.percent + '" aria-valuemin="0" aria-valuemax="100" style="width: ' + file.percent + '%;">\
                                    ' + file.percent + '%</div></div>';
                                },

                                UploadComplete: function (up, files) {
                                    if (files.length > 0) {
                                        document.getElementById('upload-status').innerHTML = '<div class="alert alert-success">{{ __('file.upload_completed') }}</div>';
                                        plupload.each(files, function (file) {
                                            let loc = '{{ route('myagora.files') }}' + '?file=' + file.name;
                                            location.assign(loc);
                                        });
                                    } else {
                                        document.getElementById('upload-status').innerHTML = '<div class="alert alert-danger">{{ __('file.upload_missing_file') }}</div>';
                                    }
                                },

                                Error: function (up, err) {
                                    const errorCode = err.code;
                                    const errorLog = err.message;
                                    document.getElementById('upload-status').innerHTML = `<div class="alert alert-danger">Error #${errorCode}: ${errorLog}</div>`;
                                }
                            }
                        });
                        uploader.init();
                    </script>
                </div>

                <div id="tab2" class="tab-pane fade">
                    <h3>{{ __('file.file_list') }}</h3>
                    @if(!empty($files))
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th>{{ __('common.name') }}</th>
                                <th>{{ __('file.size') }}</th>
                                <th>{{ __('common.updated_at') }}</th>
                                <th>{{ __('common.actions') }}</th>
                            </thead>
                            <tbody>
                            @foreach($files as $file)
                                <tr>
                                    <td>{{ $file['name'] }}</td>
                                    <td>{{ \App\Helpers\Util::formatBytes($file['size']) }}</td>
                                    <td>{{ $file['updated_at'] }}</td>
                                    <td>
                                        <a href="{{ route('myagora.file.download') }}?file={{ urlencode($file['name']) }}" title="{{ __('file.download') }}">
                                            <span class="btn btn-info glyphicon glyphicon-download" aria-hidden="true"></span></a>
                                        <a href="{{ route('myagora.file.delete') }}?file={{ urlencode($file['name']) }}" title="{{ __('common.delete') }}"
                                           onclick="return confirm('{{ __('file.confirm_deletion', ['filename' => $file['name']]) }}');">
                                            <span class="btn btn-danger glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            {{ __('file.no_files') }}
                        </div>
                    @endif
                </div>
            </div>

        @endif
    </div>
@endsection
