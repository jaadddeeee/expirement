<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        let employeeData = [];
        let allowSuper = @json(auth()->user()->AllowSuper);

        function fetchEmployees(campus) {
            $.ajax({
                url: "/varsity/employees-campus",
                method: 'post',
                cache: false,
                data: {
                    id: campus
                },
                beforeSend: function() {
                    $("#EmployeeList").empty();
                    $("#Emp").val("");
                    $("#EmployeeDropdown").hide();
                },
                success: function(data) {
                    employeeData = data;
                },
                error: function(response) {
                    $("#EmployeeList").empty().append(
                        '<li class="list-group-item text-red">Error fetching employees</li>');
                    $("#EmployeeDropdown").show();
                }
            });
        }

        if (allowSuper == 1) {
            $(document).on("change", "#Campus", function(e) {
                let campus = $(this).val();
                fetchEmployees(campus);
            });
        } else {
            let userCampus = "{{ session('campus') }}";
            fetchEmployees(userCampus);
        }

        $(document).on("input", "#Emp", function() {
            let searchValue = $(this).val().toLowerCase();
            let filteredEmployee = employeeData.filter(emp =>
                (emp.LastName + ", " + emp.FirstName + " " + (emp.MiddleName || "")).toLowerCase()
                .includes(searchValue)
            );

            if (filteredEmployee.length > 0) {
                populateEmployeeDropdown(filteredEmployee);
                $("#EmployeeDropdown").show();
            } else {
                $("#EmployeeDropdown").hide();
            }
        });

        function populateEmployeeDropdown(data) {
            $("#EmployeeList").empty();
            $.each(data, function(i, item) {
                let middleInitial = item.MiddleName && item.MiddleName.length > 0 ? item.MiddleName :
                    "";
                $("#EmployeeList").append(
                    '<li class="list-group-item employee-item" data-id="' + item.id + '">' +
                    '<a href="#" class="text-decoration-none text-dark d-block p-2">' +
                    item.LastName + ', ' + item.FirstName + (middleInitial ? ', ' + middleInitial :
                        '') +
                    '</a></li>'
                );
            });
        }

        $(document).on("click", ".employee-item", function(e) {
            e.preventDefault();
            let selectedText = $(this).text();
            let employeeID = $(this).data('id');
            $("#Emp").val(selectedText);
            $("#EmployeeID").val(employeeID);
            $("#EmployeeDropdown").hide();
        });

        $(document).click(function(e) {
            if (!$(e.target).closest("#Emp, #EmployeeDropdown").length) {
                $("#EmployeeDropdown").hide();
            }
        });
    });

    $('#search').on('input', function() {
        var query = $(this).val();
        var filterCampus = $('#filterCampus').val();

        $.ajax({
            url: '{{ route('coaches') }}',
            method: 'GET',
            data: {
                search: query,
                filterCampus: filterCampus
            },
            success: function(response) {
                setTimeout(function() {
                    $('#data').html(response.html);

                    // Hide pagination if there are fewer results than per-page limit
                    if ($('#data').find('.coaches-row').length < 10) {
                        $('.pagination').hide();
                    } else {
                        $('.pagination').show();
                    }
                }, 1000);
            }
        });
    }); 

    $(document).ready(function() {
        let allowSuper = @json(auth()->user()->AllowSuper);

        function fetchEvents(campus) {
            $.ajax({
                url: "/varsity/event-list",
                method: "POST",
                data: {
                    id: campus
                },
                beforeSend: function() {
                    $("#Event").html('<option value="0">Loading...</option>');
                },
                success: function(response) {
                    $("#Event").empty().append('<option value="0"></option>');

                    if (response.length > 0) {
                        $.each(response, function(i, item) {
                            $("#Event").append(
                                `<option value="${item.id}">${item.event}</option>`
                            );
                        });
                    } else {
                        $("#Event").append('<option value="0">No events available</option>');
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.error || "An error occurred.";
                    $("#Event").html('<option value="0">' + errorMsg + '</option>');
                }
            });
        }

        if (allowSuper == 1) {
            $(document).on("change", "#Campus", function(e) {
                let campus = $(this).val();
                fetchEvents(campus);
            });
        } else {
            let userCampus = "{{ session('campus') }}";
            fetchEvents(userCampus);
        }
    });

    $(document).on("click", "#btn-save", function(e) {
        e.preventDefault();

        var formData = new FormData($("#frmAdd")[0]);
        
        $.ajax({
            url: '/varsity/save-coach',
            method: 'post',
            data: formData,
            processData: false, // Prevent jQuery from processing FormData
            contentType: false, // Ensure correct content type for file upload
            cache: false,
            beforeSend: function() {
                $("#btn-save").prop("disabled", true);
                $("#btn-save").html("<i class = 'spinner-grow spinner-grow-sm'></i> Adding...");
                $("#msg").html("");
                $(".modal-dialog").removeClass(
                    "border border-danger rounded"); // Add red border on error
            },
            success: function(data) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.Message,
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });

                $("#modalCoach").modal('hide');

                $("#btn-save").prop("disabled", false);
                $("#btn-save").html("Add to list");

                if (data.Error == 0) {
                    setTimeout(function() {
                        $("#Campus").val(0);
                        $("#Employee").val("");
                        $("#Event").val(0);
                        $("#coachType").val(0);
                        $('input[name="Campus"]').focus();
                    }, 1000);
                }
                // $("#outAjax").html(data);
            },
            error: function(response) {
                var errors = response.responseJSON.Error;

                $("#btn-save").prop("disabled", false);
                $("#btn-save").html("Add to list");

                $(".modal-dialog").addClass(
                    "border border-danger rounded"); // Add red border on error
                $("#msg").html(errors);
            }
        });
    });

    function fetchEventsUP(selectedEventId = null) {
        var campus = $('#updateCampus').val();
        $.ajax({
            url: "/varsity/event-list",
            method: "POST",
            data: {
                id: campus
            },
            beforeSend: function() {
                $("#updateEvent").html('<option value="0">Loading...</option>');
            },
            success: function(response) {
                $("#updateEvent").empty().append('<option value="0"></option>');

                if (response.length > 0) {
                    $.each(response, function(i, item) {
                        $("#updateEvent").append(
                            `<option value="${item.id}" ${
                            item.event == selectedEventId ? "selected" : ""
                        }>${item.event}</option>`
                        );
                    });
                } else {
                    $("#updateEvent").append('<option value="0">No events available</option>');
                }

                // Show the modal only after events are loaded
                $("#updateModalCoach").modal("toggle");
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || "An error occurred.";
                $("#updateEvent").html(`<option value="0">${errorMsg}</option>`);
            },
        });
    }

    $(document).on("click", ".deleteCoach", function(e) {
        e.preventDefault();
        let id = $(this).attr("cid");
        let campus = $('#filterCampus').val();
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
                    url: '/varsity/delete-coach',
                    method: 'post',
                    data: {
                        id,
                        campus: campus
                    },
                    beforeSend: function() {
                        Swal.fire({
                            position: "Successfully",
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

    $(document).on("click", ".editCoach", function(e) {
        e.preventDefault();
        let id = $(this).attr('cid');
        var filterCampus = $('#filterCampus').val();
        $.ajax({
            url: `/varsity/edit-coach/${id}`,
            method: 'GET',
            data: {
                campus: filterCampus
            },
            success: function(data) {
                $("#hiddentID").val(id);
                $('#updateCampus').val(data.campus);
                $('#updateEmp').val(data.emp);
                $('#updateCT').val(data.coachType);
                fetchEventsUP(data.event)
                $("#updateModalCoach").modal('toggle');
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
    });

    $(document).on("click", "#btn-update", function(e) {
        e.preventDefault();
        let campus = $("#filterCampus").val();
        let emp = $("#updateEmp").val();
        $.ajax({
            url: "{{ route('update-coach') }}",
            method: 'post',
            data: $("#frmUpdate").serialize() + "&id=" + encodeURIComponent(campus) + "&Emp=" +
                encodeURIComponent(emp),
            cache: false,
            beforeSend: function() {
                $("#btn-update").prop("disabled", true);
                $("#btn-update").html("<i class = 'spinner-grow spinner-grow-sm'></i> Updating...");
                $("#msg").html("");
                $(".modal-dialog").removeClass(
                    "border border-danger rounded"); // Add red border on error
            },
            success: function(data) {

                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: data.Message,
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });

                $("#updateModalCoach").modal('hide');

                $("#btn-update").prop("disabled", false);
                $("#btn-update").html("Update the list");

                if (data.Error == 0) {
                    setTimeout(function() {
                        $("#updateCampus").val("");
                        $("#updateEmp").val("");
                        $("#updateEvent").val(0);
                        $("#updateCT").val(0);
                    }, 1000);

                }
                // $("#outAjax").html(data);
            },
            error: function(response) {
                var errors = response.responseJSON.Error;

                $("#btn-update").prop("disabled", false);
                $("#btn-update").html("Update the list");

                $(".modal-dialog").addClass(
                    "border border-danger rounded"); // Add red border on error
                $("#updatemsg").html(errors);
            }

        });
    });

    $(document).on('click', '.addCoach', function(e) {
        e.preventDefault();

        let campus = $('#filterCampus').val();
        let $this = $(this);
        let employeeID = $(this).attr('cid'); // Get encrypted Employee ID
        if ($this.attr('data-exists') === 'true') return;

        $.ajax({
            url: '/varsity/coach-list',
            method: 'POST',
            data: {
                campus: campus,
                EmployeeID: employeeID,
            },
            beforeSend: function() {
                $this.addClass('disabled').css({
                    "pointer-events": "none",
                    "opacity": "1",
                    "cursor": "not-allowed"
                }).html("<i class='spinner-grow spinner-grow-sm'></i>"); // Show loading
            },
            success: function(response) {

                // Change the icon to bx-check-circle
                $this.attr('data-exists', 'true').html("<i class='bx bx-check-circle text-success'></i>");
                location.reload(); // Reload the page after success
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON.error || "An error occurred";
                $this.removeClass('disabled').css({
                    "pointer-events": "auto",
                    "opacity": "1",
                    "cursor": "pointer"
                }).html("<i class='bx bx-plus-circle'></i>");
            }
        });
});

</script>
