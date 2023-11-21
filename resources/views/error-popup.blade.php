@if ($errors->any())
    <script>
        Swal.fire({
        icon: 'error',
        html: `<div class="alert alert-danger"><ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                </ul></div>`,
        confirmButtonColor: '#666666',
        })
    </script>
@endif
