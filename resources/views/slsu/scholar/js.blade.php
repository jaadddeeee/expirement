<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // search scholar
    $('#searchScholar').on('input', function() {
        let query = $(this).val();

        $.ajax({
            url: `{{ route('search-scholar') }}`,
            type: 'GET',
            data: {
                query: query
            },
            success: function(response) {
                if (response.html) {
                    $('table tbody').html(response.html);
                }
            },
            error: function(xhr) {
                console.error('Error fetching scholars:', xhr);
            }
        });
    });




    $(document).ready(function() {

        // Search student
        $('#searchStudent').on('input', function() {
            let query = $(this).val();

            $.ajax({
                url: `{{ route('search-student') }}`,
                type: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    if (response.html) {
                        $('table tbody').html(response.html);
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching students:', xhr);
                }
            });
        });

        $(document).on('click', '.add-scholar', function() {
            let studentNo = $(this).data('studentno');
            let studentName = $(this).data('studentname');

            // Set values in offcanvas form
            $('input[name="studentNo"]').val(studentNo);
            $('input[name="studentName"]').val(studentName);


            let offcanvas = new bootstrap.Offcanvas($('#offcanvasAddScholar'));
            offcanvas.show();
        });


        $("#frmAddScholar").on("submit", function(e) {
            e.preventDefault();

            $.ajax({
                url: "/scholarship-new/add-scholars",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#msg").html('<div class="alert alert-success">' + response
                            .message + '</div>');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        $("#msg").html('<div class="alert alert-danger">' + response
                            .message + '</div>');
                    }
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message ?? 'Something went wrong!';
                    $("#msg").html('<div class="alert alert-danger">' + errorMessage +
                        '</div>');
                }
            });
        });
    });
</script>
