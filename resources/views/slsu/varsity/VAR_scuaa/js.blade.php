<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#btn-save").on("click", function(e) {
        let campus = $("#filterCampus").val();
        e.preventDefault();
        $.ajax({
            url: '/varsity/set-scuaa',
            method: 'post',
            data: $("#frmSet").serialize() + "&id=" + encodeURIComponent(campus),
            cache: false,
            beforeSend: function() {
                $("#btn-save").prop("disabled", true);
                $("#btn-save").html("<i class='spinner-grow spinner-grow-sm'></i> Adding...");
                $("#msg").html("");
                // $(".modal-body").removeClass("border border-danger rounded");
            },
            success: function(data) {
                Swal.fire({
                    title: 'SCUAA Set!',
                    text: data.message,
                    icon: 'success',
                });

                $("#modalList").modal('hide');

                $("#btn-save").prop("disabled", false);
                $("#btn-save").html("Add to list");

                setTimeout(function() {
                    $("#Event").val(0);
                    $("#totalPart").val("");
                }, 1000);

            },
            error: function(response) {
                var errors = response.responseJSON.Error;

                $("#btn-save").prop("disabled", false);
                $("#btn-save").html("Add to list");

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
</script>
