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

    $('#searchAthletes').on('input', function() {
        var query = $(this).val();
        var filterEvent = $('#filterEvent').val();
        var filterSchoolYear = $('#filterSchoolYear').val();

        $.ajax({
            url: '{{ route('scuaa-athletes') }}',
            method: 'GET',
            data: {
                searchAthletes: query,
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
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#searchCoach').on('input', function() {
        var query = $(this).val();
        var filterEvent = $('#filterEvent').val();
        var filterSchoolYear = $('#filterSchoolYear').val();

        $.ajax({
            url: '{{ route('scuaa-coaches') }}', // Ensure this route is correct
            method: 'GET',
            data: {
                searchCoach: query, // Ensure this matches the request parameter in your controller
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
            },
            error: function(xhr) {
                console.error(xhr.responseText);
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

    $("#btnGenerateList").on("click", function (e) {
        e.preventDefault();

        var filterEvent = $("#filterEvent").val();
        var filterSchoolYear = $("#filterSchoolYear").val();

        $.ajax({
            url: "{{ route('generate.list') }}",
            method: "GET",
            data: {
                filterEvent: filterEvent,
                filterSchoolYear: filterSchoolYear,
            },
            success: function (response) {
                window.open(response.url, '_blank'); // Open the generated PDF
            },
            error: function (xhr) {
            // Parse the error response
                var response = xhr.responseJSON;

                // Show SweetAlert with the error message
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: response.Error,
                });
            },
        });
    });

    $("#btnGenerateChecklist").on("click", function (e) {
        e.preventDefault();

        var filterEvent = $("#filterEvent").val();
        var filterSchoolYear = $("#filterSchoolYear").val();

        $.ajax({
            url: "{{ route('generate.checklist') }}",
            method: "GET",
            data: {
                filterEvent: filterEvent,
                filterSchoolYear: filterSchoolYear,
            },
            success: function (response) {
                window.open(response.url, '_blank'); // Open the generated PDF
            },
            error: function (xhr) {
            // Parse the error response
                var response = xhr.responseJSON;

                // Show SweetAlert with the error message
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: response.Error,
                });
            },
        });
    });

    // $("#generateEli").on("click", function (e) {
    //     e.preventDefault();
    //     let id = $(this).attr("cid");
    //     $.ajax({
    //         url: "{{ route('generate.eligibility') }}",
    //         method: "GET",
    //         data: {
    //             id,
    //         },
    //         success: function (response) {
    //             window.open(response.url, '_blank'); // Open the generated PDF
    //         },
    //         error: function (xhr) {
    //         // Parse the error response
    //             var response = xhr.responseJSON;

    //             // Show SweetAlert with the error message
    //             Swal.fire({
    //                 icon: "error",
    //                 title: "Error!",
    //                 text: response.Error,
    //             });
    //         },
    //     });
    // });

    $(document).on("click", "#deleteCoaches", function(e) {
        e.preventDefault();
        let id = $(this).attr("cid");
        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the coach select. You can't revert this.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Delete",
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                $.ajax({
                    url: "/varsity/delete-coaches",
                    method: 'post',
                    data: {
                        id,
                    },
                    beforeSend: function() {
                        Swal.fire({
                            position: "center",
                            icon: "info",
                            title: "Deleting...",
                            showConfirmButton: false
                        });
                    },
                    success: function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.Message,
                        }).then(() => {
                            window.location.reload();
                        });
                        // window.location.reload();
                    },
                    error: function(response) {

                        if (response.status == 419) {
                            window.location.reload();
                        } else {
                            var errors = response.responseJSON.errors;

                            Swal.fire(
                                'Error!',
                                errors,
                                'error'
                            );
                        }
                    }
                });
            }
        });
    })

    $(document).on("click", "#deleteAthletes", function(e) {
        e.preventDefault();
        let id = $(this).attr("cid");
        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the coach select. You can't revert this.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Delete",
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                $.ajax({
                    url: "/varsity/delete-athletes",
                    method: 'post',
                    data: {
                        id,
                    },
                    beforeSend: function() {
                        Swal.fire({
                            position: "center",
                            icon: "info",
                            title: "Deleting...",
                            showConfirmButton: false
                        });
                    },
                    success: function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.Message,
                        }).then(() => {
                            window.location.reload();
                        });
                        // window.location.reload();
                    },
                    error: function(response) {

                        if (response.status == 419) {
                            window.location.reload();
                        } else {
                            var errors = response.responseJSON.errors;

                            Swal.fire(
                                'Error!',
                                errors,
                                'error'
                            );
                        }
                    }
                });
            }
        });
    })
</script>
