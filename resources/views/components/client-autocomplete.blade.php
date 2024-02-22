<link rel="stylesheet" href="{{ secure_asset('css/jquery-ui.css') }}">
<script src="{{ secure_asset('js/jquery-ui.min.js') }}"></script>

<script>
    $(function () {
        $("#client_name").autocomplete({
            source: function (request, response) {
                if (request.term.length >= 3) {
                    $.ajax({
                        url: '{{ route("clients.search") }}',
                        dataType: 'json',
                        data: {
                            keyword: request.term
                        },
                        success: function (data) {
                            response(data.map(function (client) {
                                return {
                                    label: client.name + ' - ' + client.code,
                                    value: client.code
                                }
                            }));
                        }
                    });
                } else {
                    response([]);
                }
            },
            minLength: 3,
            select: function (event, ui) {
                $("#client_name").val(ui.item.label);
                return false;
            }
        });
    });
</script>
