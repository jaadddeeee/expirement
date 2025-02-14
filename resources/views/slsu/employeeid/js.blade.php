<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#search').on('input', function() {
        var searchQuery = $(this).val();

        $.ajax({
            url: "{{ route('employee-list') }}",
            method: "GET",
            data: {
                search: searchQuery
            },
            success: function(response) {
                $('#employee-table').html(response);

                if (searchQuery) {
                    window.history.pushState(null, '',
                        "{{ route('employee-list') }}?search=" + searchQuery);
                } else {
                    window.history.pushState(null, '', "{{ route('employee-list') }}");
                }
            }
        });
    });
</script>
