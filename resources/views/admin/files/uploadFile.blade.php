<div id="uploadField">
    <span class="btn btn-primary" onclick="$('#uploadFileForm').toggleClass('hidden')">
        {{ __('file.upload_file') }}
    </span>

    <form id="uploadFileForm" class="hidden" method="post" enctype="multipart/form-data"
          action="{{ route('files.upload', base64_encode($directory)) }}"
          style="padding: 20px;">
        @csrf
        <input type="file" name="file" id="file" class="btn btn-default" style="display: inline">
        <input type="submit" class="btn btn-default" value="{{ __('file.upload') }}">
    </form>
</div>
