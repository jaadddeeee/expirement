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

        const toggleSwitch = `
            <label class="form-label">Date Screening:</label>
            <input type="text" name="screenDate" id="dateRange" placeholder="YYYY-MM-DD to YYYY-MM-DD" class="form-control flatpickr-input text-decoration-none">
        `;

        Swal.fire({
            title: "Generate Official List",
            width: 390,
            html: toggleSwitch,
            showCancelButton: true,
            confirmButtonText: "Generate",
            didOpen: () => {
                flatpickr("#dateRange", {
                    mode: "range",
                    dateFormat: "Y-m-d", 
                    minDate: "today", 
                });
        },
        }).then((result) => {
        if (result.isConfirmed) {

            let filterEvent = $("#filterEvent").val();
            let filterSchoolYear = $("#filterSchoolYear").val();
            let dateRange = $("#dateRange").val(); // Get the selected date range

            if(dateRange == "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select Date Screening!',
                });
                return;
            }

            let url = "{{ route('generate.list') }}" +
                "?filterSchoolYear=" + encodeURIComponent(filterSchoolYear) +
                "&filterEvent=" + encodeURIComponent(filterEvent) + 
                "&date=" + encodeURIComponent(dateRange);

            // Open the generated certificate in a new tab (forces the download)
            window.open(url, '_blank');
            }
        });
    });

    $("#btnGenerateChecklist").on("click", function (e) {
        e.preventDefault();

        const toggleSwitch = `
            <label class="form-label">Date Screening:</label>
            <input type="text" name="screenDate" id="dateRange" placeholder="YYYY-MM-DD to YYYY-MM-DD" class="form-control flatpickr-input text-decoration-none">
        `;

        Swal.fire({
            title: "Generate Check List",
            width: 390,
            html: toggleSwitch,
            showCancelButton: true,
            confirmButtonText: "Generate",
            didOpen: () => {
                flatpickr("#dateRange", {
                    mode: "range",
                    dateFormat: "Y-m-d", 
                    minDate: "today", 
                });
        },
        }).then((result) => {
        if (result.isConfirmed) {

            let filterEvent = $("#filterEvent").val();
            let filterSchoolYear = $("#filterSchoolYear").val();
            let dateRange = $("#dateRange").val(); // Get the selected date range

            if(dateRange == "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select Date Screening!',
                });
                return;
            }

            let url = "{{ route('generate.checklist') }}" +
                "?filterSchoolYear=" + encodeURIComponent(filterSchoolYear) +
                "&filterEvent=" + encodeURIComponent(filterEvent) + 
                "&date=" + encodeURIComponent(dateRange);

            // Open the generated certificate in a new tab (forces the download)
            window.open(url, '_blank');
            }
        });
    });

    $(document).on("click", "#generateEli", function () {
        const toggleSwitch = `
            <label class="switch" style="margin: 10px 0;">
                <input type="checkbox" id="generateToggle">
                <span class="slider"></span>
            </label>
        `;

        // Display SweetAlert with the toggle switch
        Swal.fire({
            title: "Generate Eligibility",
            width: 390,
            text: "In Campus / Off Campus",
            html: '<small id="toggleText">Off Campus</small><br>' + toggleSwitch,
            showCancelButton: true,
            confirmButtonText: "Generate",
            didOpen: () => {
                const toggle = document.querySelector("#generateToggle");
                const toggleText = document.querySelector("#toggleText");

                // Add event listener to the toggle switch
                toggle.addEventListener("change", function () {
                    if (this.checked) {
                        toggleText.innerHTML = "In Campus";
                    } else {
                        toggleText.innerHTML = "Off Campus";
                    }
                });
            },
        }).then((result) => {
        if (result.isConfirmed) {

            let toggleState = document.querySelector("#generateToggle").checked ? 1 : 0;
            let id = $(this).attr("cid");

            let url = "{{ route('generate.eligibility') }}" +
                "?id=" + encodeURIComponent(id) +
                "&status=" + encodeURIComponent(toggleState);

            // Open the generated certificate in a new tab (forces the download)
            window.open(url, '_blank');
            }
        });
    });

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
