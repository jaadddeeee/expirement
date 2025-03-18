<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#btn-saveEvent").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '/varsity/set-event',
            method: 'post',
            data: $("#frmSet").serialize(),
            cache: false,
            beforeSend: function() {
                $("#btn-saveEvent").prop("disabled", true);
                $("#btn-saveEvent").html("<i class='spinner-grow spinner-grow-sm'></i> Adding...");
                $("#msg").html("");
                // $(".modal-body").removeClass("border border-danger rounded");
            },
            success: function(data) {
                Swal.fire({
                    title: 'Event Set!',
                    text: data.message,
                    icon: 'success',
                });

                $("#modalList").modal('hide');

                $("#btn-saveEvent").prop("disabled", false);
                $("#btn-saveEvent").html("Add to list");

                setTimeout(function() {
                    $("#Event").val(0);
                    $("#totalPart").val("");
                }, 1000);

            },
            error: function(response) {
                var errors = response.responseJSON.Error;

                $("#btn-saveEvent").prop("disabled", false);
                $("#btn-saveEvent").html("Add to list");

                $(".modal-dialog").addClass("border border-danger rounded");
                $("#msg").html(errors);
            }
        });
    });

    $('#search').on('input', function() {
        var query = $(this).val();
        var filterEvent = $('#filterEvent').val();
        var filterSchoolYear = $('#filterSchoolYear').val();

        $.ajax({
            url: '{{ route('scuaa') }}',
            method: 'GET',
            data: {
                search: query,
                filterEvent: filterEvent,
                filterSchoolYear: filterSchoolYear,
            },
            success: function(response) {
                setTimeout(function() {
                    $('#data').html(response.html);

                    // Hide pagination if there are fewer results than per-page limit
                    if ($('#data').find('.varsity-row').length < 10) {
                        $('.pagination').hide();
                    } else {
                        $('.pagination').show();
                    }
                }, 1000);
            }
        });
    });

    flatpickr("#date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
    });

    $("#btn-saveScuaa").on("click", function(e) {
        e.preventDefault();

        var formData = new FormData($("#frmSetScuaa")[0]);

        $.ajax({
            url: "/varsity/set-scuaa",
            method: "POST",
            data: formData,
            processData: false, // Prevent jQuery from processing FormData
            contentType: false, // Ensure correct content type for file upload
            cache: false,
            beforeSend: function() {
                $("#btn-saveScuaa").prop("disabled", true).html(
                    "<i class='spinner-grow spinner-grow-sm'></i> Adding...");
                $("#msg").html("");
            },
            success: function(data) {
                Swal.fire({
                    title: "Scuaa Set!",
                    text: data.message,
                    icon: "success",
                });

                $("#scuaaModal").modal("hide");

                // Reset form fields properly after success
                form.reset();
                $("#btn-saveScuaa").prop("disabled", false).html("Add to list");
            },
            error: function(xhr) {
                $("#btn-saveScuaa").prop("disabled", false).html("Add to list");

                var response = xhr.responseJSON;
                if (response && response.error) {
                    $("#msg").html(response.error);
                } else {
                    $("#msg").html("An unexpected error occurred. Please try again.");
                }

                $(".modal-dialog").addClass("border border-danger rounded");
            }
        });
    });
</script>
