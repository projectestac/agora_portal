<form action="{{ route('files.upload', base64_encode($directory)) }}" method="post" enctype="multipart/form-data">
    @csrf
    <label for="file">Seleccionar fichero:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Subir fichero">
</form>
