<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        let studentData = [];
        let allowSuper = @json(auth()->user()->AllowSuper);

        function fetchStudents(campus) {
            $.ajax({
                url: "/varsity/student-campus",
                method: 'post',
                cache: false,
                data: {
                    id: campus
                },
                beforeSend: function() {
                    $("#StudentList").empty();
                    $("#Stud").val("");
                    $("#StudentDropdown").hide();
                },
                success: function(data) {
                    studentData = data;
                },
                error: function(response) {
                    $("#StudentList").empty().append(
                        '<li class="list-group-item text-red">Error fetching students</li>');
                    $("#StudentDropdown").show();
                }
            });
        }

        if (allowSuper == 1) {
            $(document).on("change", "#Campus", function(e) {
                let campus = $(this).val();
                fetchStudents(campus);
            });
        } else {
            let userCampus = "{{ session('campus') }}";
            fetchStudents(userCampus);
        }

        $(document).on("input", "#Stud", function() {
            let searchValue = $(this).val().toLowerCase();
            let filteredStudent = studentData.filter(emp =>
                (emp.LastName + ", " + emp.FirstName + " " + (emp.MiddleName || "")).toLowerCase()
                .includes(searchValue)
            );

            if (filteredStudent.length > 0) {
                populateStudentDropdown(filteredStudent);
                $("#StudentDropdown").show();
            } else {
                $("#StudentDropdown").hide();
            }
        });

        function populateStudentDropdown(data) {
            $("#StudentList").empty();
            $.each(data, function(i, item) {
                let middleInitial = item.MiddleName && item.MiddleName.length > 0 ? item.MiddleName :
                "";
                $("#StudentList").append(
                    '<li class="list-group-item student-item" data-id="' + item.id + '">' +
                    '<a href="#" class="text-decoration-none text-dark d-block p-2">' +
                    item.LastName + ', ' + item.FirstName + (middleInitial ? ', ' + middleInitial :
                        '') +
                    '</a></li>'
                );
            });
        }

        $(document).on("click", ".student-item", function(e) {
            e.preventDefault();
            let selectedText = $(this).text();
            let studentId = $(this).data('id');
            $("#Stud").val(selectedText);
            $("#StudentNo").val(studentId);
            $("#StudentDropdown").hide();
        });

        $(document).click(function(e) {
            if (!$(e.target).closest("#Stud, #StudentDropdown").length) {
                $("#StudentDropdown").hide();
            }
        });
    });

    $(document).ready(function() {
        let allowSuper = @json(auth()->user()->AllowSuper);

        function fetchEvents(campus) {
            $.ajax({
                url: "/varsity/student-event-list",
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

    // $('#search').on('input', function() {
    //     var query = $(this).val();
    //     var filterCampus = $('#filterCampus').val();
    //     var filterSchoolYear = $('#filterSchoolYear').val();
    //     var filterSemester = $('#filterSemester').val();

    //     $.ajax({
    //         url: '{{ route('varsity') }}',
    //         method: 'GET',
    //         data: {
    //             search: query,
    //             filterCampus: filterCampus,
    //             filterSchoolYear: filterSchoolYear,
    //             filterSemester: filterSemester
    //         },
    //         success: function(response) {
    //             setTimeout(function() {
    //                 $('#data').html(response.html);

    //                 // Hide pagination if there are fewer results than per-page limit
    //                 if ($('#data').find('.varsity-row').length < 10) {
    //                     $('.pagination').hide();
    //                 } else {
    //                     $('.pagination').show();
    //                 }
    //             }, 1000);
    //             restoreCheckboxStates();
    //         }
    //     });
    // });

    $("#btn-save").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '/varsity/save-varsity',
            method: 'post',
            data: $("#frmAdd").serialize(),
            cache: false,
            beforeSend: function() {
                $("#btn-save").prop("disabled", true);
                $("#btn-save").html("<i class = 'spinner-grow spinner-grow-sm'></i> Adding...");
                $("#msg").html("");
                $(".modal-dialog").removeClass(
                    "border border-danger rounded"); // Add red border on error
                $(".form-control, .form-select").removeClass(
                    "border border-danger"); // Remove error borders from inputs
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

                $("#modalVarsity").modal('hide');

                $("#btn-save").prop("disabled", false);
                $("#btn-save").html("Add to list");

                if (data.Error == 0) {
                    setTimeout(function() {
                        $("#Campus").val(0);
                        $("#Stud").val("");
                        $("#Event").val(0);
                        $("#SchoolYear").val(0);
                        $("#Semester").val(0);
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
            url: "/varsity/student-event-list",
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

    $(".deleteVarsity").on("click", function(e) {
        e.preventDefault();
        let id = $(this).attr("cid");
        let campus = $('#filterCampus').val();
        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the varsity select. You can't revert this.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Delete",
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                $.ajax({
                    url: '/varsity/delete-varsity',
                    method: 'post',
                    data: {
                        id,
                        campus: campus
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
                            var errors = response.responseJSON.error;

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

    $(".editVarsity").on("click", function(e) {
        e.preventDefault();
        let id = $(this).attr('cid');
        var filterCampus = $('#filterCampus').val();

        $.ajax({
            url: `/varsity/edit-varsity/${id}`,
            method: 'GET',
            data: {
                campus: filterCampus
            },
            success: function(data) {
                $("#hiddentID").val(id);
                $('#updateCampus').val(data.campus);
                $('#updateStud').val(data.stud);
                fetchEventsUP(data.event);
                $('#updateSY').val(data.sy);
                $('#updateSem').val(data.sem);
                $("#updateModalVar").modal('toggle');
            },
            error: function(response) {
                if (response.status == 419) {
                    window.location.reload();
                } else {
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
                }
            }
        });
    });

    $("#btn-update").on("click", function(e) {
        e.preventDefault();
        let campus = $("#filterCampus").val();
        $.ajax({
            url: "{{ route('update-varsity') }}",
            method: 'post',
            data: $("#frmUpdate").serialize() + "&id=" + encodeURIComponent(campus),
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

                $("#updateModalVar").modal('hide');

                $("#btn-update").prop("disabled", false);
                $("#btn-update").html("Update the list");

                if (data.Error == 0) {

                    setTimeout(function() {
                        $("#updateCampus").val("");
                        $("#updateStud").val("");
                        $("#updateEvent").val(0);
                        $("#updateSY").val(0);
                        $("#updateSem").val(0);
                    }, 1000);
                }
            },
            error: function(response) {
                var errors = response.responseJSON.Error;

                $("#btn-update").prop("disabled", false);
                $("#btn-update").html("Update the list");

                $(".modal-dialog").addClass(
                    "border border-danger rounded");
                $("#updatemsg").html(errors);
            }

        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var selectAllCheckbox = document.getElementById('select-all');
        const selectedCountElement = document.getElementById('selected-row');

        function getCheckboxes() {
            return document.querySelectorAll('.select-row');
        }

        function selectedCount() {
            var checkboxes = getCheckboxes();
            var checkedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;

            // If no checkboxes are selected, set the count to null or an empty string
            if (checkedCount === 0) {
                selectedCountElement.textContent = "";
                selectedCountElement.classList.remove("border", "px-1", "rounded", "text-dark");
                selectedCountElement.style.border = "none";
            } else {
                selectedCountElement.textContent = checkedCount;
                selectedCountElement.classList.add("px-1", "rounded", "text-dark");
                selectedCountElement.style.border = "1px solid rgba(0, 0, 0, 0.3)"; // Add border with 50% opacity
            }
        }

        function restoreCheckboxStates() {
            getCheckboxes().forEach(checkbox => {
                var storedValue = localStorage.getItem('checkbox_' + checkbox.value);
                checkbox.checked = storedValue === 'true';
            });
            updateSelectAllState();
        }

        function updateSelectAllState() {
            var checkboxes = getCheckboxes();
            var checkedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
            var totalCount = checkboxes.length;

            selectAllCheckbox.checked = checkedCount > 0 && checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;

            localStorage.setItem('selectAllChecked', selectAllCheckbox.checked);
        }

        // Handle "Select All" checkbox change
        selectAllCheckbox.addEventListener('change', function () {
            var isChecked = this.checked;
            getCheckboxes().forEach(checkbox => {
                checkbox.checked = isChecked;
                localStorage.setItem('checkbox_' + checkbox.value, isChecked);
            });
            selectedCount();
        });

        // Handle individual row checkbox changes
        document.addEventListener('change', function (event) {
            if (event.target.classList.contains('select-row')) {
                localStorage.setItem('checkbox_' + event.target.value, event.target.checked);
                updateSelectAllState();
                selectedCount();
            }
        });

        // Restore checkbox states on page load
        restoreCheckboxStates();
        selectedCount();

        // Handle pagination: Restore checkbox states when the page changes
        document.addEventListener('pageChange', function () {
            setTimeout(restoreCheckboxStates, 100); // Slight delay to allow new checkboxes to load
        });
    });

    function getCheckboxes() {
        return document.querySelectorAll('.select-row');
    }

    function restoreCheckboxStates() {
        getCheckboxes().forEach(checkbox => {
            var storedValue = localStorage.getItem('checkbox_' + checkbox.value);
            checkbox.checked = storedValue === 'true';
        });
        updateSelectAllState();
    }

    function updateSelectAllState() {
        var storedCheckboxes = getAllStoredCheckboxes();
        var checkedCount = storedCheckboxes.filter(c => c.checked).length;
        var totalCount = storedCheckboxes.length;

        selectAllCheckbox.checked = checkedCount > 0 && checkedCount === totalCount;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;

        localStorage.setItem('selectAllChecked', selectAllCheckbox.checked);
    }
    
    // document.addEventListener('DOMContentLoaded', function() {
    //     var selectAllCheckbox = document.getElementById('select-all');

    //     function getCheckboxes() {
    //         return document.querySelectorAll('.select-row');
    //     }

    //     function getAllStoredCheckboxes() {
    //         return Object.keys(localStorage)
    //             .filter(key => key.startsWith('checkbox_'))
    //             .map(key => ({
    //                 value: key.replace('checkbox_', ''),
    //                 checked: localStorage.getItem(key) === 'true'
    //             }));
    //     }

    //     function restoreCheckboxStates() {
    //         getCheckboxes().forEach(checkbox => {
    //             var storedValue = localStorage.getItem('checkbox_' + checkbox.value);
    //             checkbox.checked = storedValue === 'true';
    //         });
    //         updateSelectAllState();
    //     }

    //     function updateSelectAllState() {
    //         var storedCheckboxes = getAllStoredCheckboxes();
    //         var checkedCount = storedCheckboxes.filter(c => c.checked).length;
    //         var totalCount = storedCheckboxes.length;

    //         selectAllCheckbox.checked = checkedCount > 0 && checkedCount === totalCount;
    //         selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;

    //         localStorage.setItem('selectAllChecked', selectAllCheckbox.checked);
    //     }

    //     // Restore states on page load
    //     restoreCheckboxStates();

    //     // Select All should apply across all pages
    //     selectAllCheckbox.addEventListener('change', function() {
    //         var isChecked = this.checked;

    //         // Update checkboxes on the current page
    //         getCheckboxes().forEach(checkbox => {
    //             checkbox.checked = isChecked;
    //             localStorage.setItem('checkbox_' + checkbox.value, isChecked);
    //         });

    //         // Apply selection across pages by updating localStorage
    //         getAllStoredCheckboxes().forEach(checkbox => {
    //             localStorage.setItem('checkbox_' + checkbox.value, isChecked);
    //         });

    //         localStorage.setItem('selectAllChecked', isChecked);
    //     });

    //     // Listen for checkbox changes and store state
    //     document.addEventListener('change', function(event) {
    //         if (event.target.classList.contains('select-row')) {
    //             localStorage.setItem('checkbox_' + event.target.value, event.target.checked);
    //             updateSelectAllState();
    //         }
    //     });

    //     // // Handle pagination: Restore checkbox states when the page changes
    //     // document.addEventListener('pageChange', function() {
    //     //     setTimeout(restoreCheckboxStates, 100); // Slight delay to allow new checkboxes to load
    //     // });
    // });

    // function getAllStoredCheckboxes() {
    //     return Object.keys(localStorage)
    //         .filter(key => key.startsWith('checkbox_'))
    //         .map(key => ({
    //             value: key.replace('checkbox_', ''),
    //             checked: localStorage.getItem(key) === 'true'
    //         }));
    // }

    // Search functionality with checkbox state restoration
    $('#search').on('input', function() {
        var query = $(this).val();
        var filterCampus = $('#filterCampus').val();
        var filterSchoolYear = $('#filterSchoolYear').val();
        var filterSemester = $('#filterSemester').val();

        $.ajax({
            url: '{{ route('varsity') }}',
            method: 'GET',
            data: {
                search: query,
                filterCampus: filterCampus,
                filterSchoolYear: filterSchoolYear,
                filterSemester: filterSemester
            },
            success: function(response) {
                setTimeout(function() {
                    $('#data').html(response.html);

                    // Restore checkbox states after updating search results
                    restoreCheckboxStates();

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

    $("#btn-list").on("click", function(e) {
        e.preventDefault();
        var campus = $('#filterCampus').val();
        const selectedCountElement = document.getElementById('selected-row');

        // Collect selected varsity IDs (including those stored across pages)
        let selectedVarsities = [];

        // Get checkboxes from the current page
        document.querySelectorAll('.select-row:checked').forEach(function(checkbox) {
            if (checkbox.value) {
                selectedVarsities.push(checkbox.value);
            }
        });

        // Also add selected checkboxes from localStorage (for pagination)
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('checkbox_')) {
                let value = key.replace('checkbox_', '');
                if (localStorage.getItem(key) === 'true' && value !== 'undefined' && value !== '') {
                    selectedVarsities.push(value);
                }
            }
        });

        // Remove duplicates (in case checkboxes exist on multiple pages)
        selectedVarsities = [...new Set(selectedVarsities)];

        $.ajax({
            url: '/varsity/student-list',
            method: 'post',
            data: JSON.stringify({
                selectedVarsities: selectedVarsities,
                campus: campus
            }),
            contentType: 'application/json',
            cache: false,
            beforeSend: function() {
                $("#msg").html("");
            },
            success: function(data) {
                if (data.success) {
                    Swal.fire(
                        'Successfully saved',
                        data.message,
                        'success'
                    ).then(result => {
                        if (result.isConfirmed) {
                            selectedCountElement.textContent = "";
                            selectedCountElement.classList.remove("border", "px-1", "rounded", "text-dark");
                            selectedCountElement.style.border = "none";
                        }

                    });

                    // Reset checkboxes on current page
                    document.querySelectorAll('.select-row:checked').forEach(function(checkbox) {
                        checkbox.checked = false;
                        localStorage.removeItem('checkbox_' + checkbox
                            .value); // Clear stored checkbox states
                    });

                    // Reset "Select All" checkbox
                    var Allcheckbox = document.getElementById('select-all');
                    if (Allcheckbox) {
                        Allcheckbox.checked = false;
                        Allcheckbox.indeterminate = false;
                    }

                    // Clear selection across all pages
                    Object.keys(localStorage).forEach(key => {
                        if (key.startsWith('checkbox_')) {
                            localStorage.removeItem(key);
                        }
                    });
                    localStorage.setItem('selectAllChecked', false);
                } else {
                    Swal.fire(
                        'Error!',
                        data.error,
                        'error'
                    );
                }
            },
            error: function(response) {
                var errors = response.responseJSON.error;
                Swal.fire({
                    title: 'Unable to save!',
                    html: errors,
                    icon: 'error',
                    confirmButtonText: '<span style="color: white;">OK</span>',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                    },
                    buttonsStyling: false
                });
            }
        });
    });
</script>
