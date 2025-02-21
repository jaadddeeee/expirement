<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // SCHOLARSHIP AJAX | FINISHED

    // fetch scholarhips | DONE
    function fetchScholarships() {
        $.ajax({
            url: "{{ route('fetch-scholarships') }}",
            method: 'GET',
            success: function(data) {
                $(".datatable tbody").html(data);
            },
            error: function(xhr) {
                console.error(`Failed to fetch scholarships: ${xhr.statusText}`);
            }
        });
    }

    // dynamic select | DONE
    $('#scholarshipType, #editScholarshipType').on('change', function() {
        let externalOptions = $(this).attr('id') === 'scholarshipType' ? $('#externalOptions') : $(
            '#editExternalOptions');
        if ($(this).val() == '2') {
            externalOptions.show();
        } else {
            externalOptions.hide();
            externalOptions.find('select').val('');
        }
    });

    // delay the execution of search by n of seconds using setTimeout | DONE
    let searchTimeout;
    $('#searchScholarship').on('input', function() {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            let query = $(this).val();

            $.ajax({
                url: "{{ route('search-scholarship') }}",
                type: "GET",
                data: {
                    query: query,
                    is_search: true
                },
                success: function(response) {
                    $('table tbody').html(response.html);
                }
            });
        }, 1000);
    }); // 1 second delay

    // save scholarship | DONE
    $(document).on("click", "#btnSaveScholarship", function(e) {
        e.preventDefault();
        $.ajax({
            url: '/scholarship-new/save-scholarship',
            method: 'POST',
            data: $("#frmAddScholarship").serialize(),
            beforeSend: function() {
                $("#btnSaveScholar")
                    .prop("disabled", true)
                    .html("<i class='spinner-grow spinner-grow-sm'></i> Adding...");
                $("#saveScholarshipMsg").html("");
            },
            success: function(response) {
                const {
                    Error,
                    Message
                } = response;

                if (Error === 0) {
                    $("#saveScholarshipMsg").html(
                        `<div class="alert alert-success">${Message}</div>`);
                    setTimeout(function() {
                        $('#offcanvasAddScholar').offcanvas('hide');
                        $("#msg").html('');
                        window.location.reload();
                    }, 1000);
                } else {
                    $("#saveScholarshipMsg").html(
                        `<div class="alert alert-danger">${Message}</div>`);
                }
            },
            error: function(xhr) {
                $("#saveScholarshipMsg").html(
                    `<div class="alert alert-danger">An unexpected error occurred: ${xhr.statusText}</div>`
                );
            },
            complete: function() {
                $("#btnSaveScholar").prop("disabled", false).html("Save");
            }
        });
    });

    // edit scholarship | DONE
    $(document).on("click", ".editScholarship", function(e) {
        e.preventDefault();

        let scholarshipID = $(this).data('scholarship-id');

        $.ajax({
            url: `/scholarship-new/edit-scholarship/${scholarshipID}`,
            method: 'GET',
            beforeSend: function() {
                $('#editMsg').html('');
            },
            success: function(response) {
                if (response.Error === 0) {
                    let scholarship = response.Scholarship;

                    $('#id').val(scholarshipID);
                    $('#editScholarshipName').val(scholarship.name);
                    $('#editScholarshipType').val(scholarship.type).change();

                    if (scholarship.type == 2) {
                        $('#editExternalOptions').show();
                        $('#editExternalScholarshipType').val(scholarship.externalType);
                    } else {
                        $('#editExternalOptions').hide();
                    }

                    $('#offcanvasEditScholarship').offcanvas('show');
                } else {
                    alert(response.Message);
                }
            },
            error: function(xhr) {
                alert('Error fetching scholarship: ' + xhr.statusText);
            }
        });
    });

    // update scholarship | DONE
    $(document).on("click", "#btnUpdateScholarship", function(e) {
        e.preventDefault();

        let formData = $("#frmEditScholarship").serialize();

        $.ajax({
            url: "{{ route('update-scholarship') }}",
            method: 'PUT',
            data: formData,
            cache: false,
            dataType: 'json',
            beforeSend: function() {
                $("#btnUpdateScholarship")
                    .prop("disabled", true)
                    .html("<i class='spinner-grow spinner-grow-sm'></i> Updating...");
                $("#editMsg").html("");
            },
            success: function(response) {
                const {
                    Error,
                    Message
                } = response;

                if (Error === 0) {
                    $("#editMsg").html(
                        `<div class="alert alert-success">${Message}</div>`);
                    setTimeout(function() {
                        $('#offcanvasEditScholarship').offcanvas('hide');
                        $("#editMsg").html('');
                        window.location.reload();
                    }, 1000);
                } else {
                    $("#editMsg").html(
                        `<div class="alert alert-danger">${Message}</div>`);
                }
            },
            error: function(xhr) {
                $("#editMsg").html(
                    `<div class="alert alert-danger">An unexpected error occurred: ${xhr.statusText}</div>`
                );
            },
            complete: function() {
                $("#btnUpdateScholarship").prop("disabled", false).html("Update");
            }
        });
    });

    // delete scholarship | DONE
    $(document).on("click", ".deleteScholarship", function(e) {
        e.preventDefault();

        let scholarshipID = $(this).data('scholarship-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('delete-scholarship') }}",
                    method: 'DELETE',
                    data: {
                        id: scholarshipID
                    },
                    success: function(response) {
                        const {
                            Error,
                            Message
                        } = response;

                        if (Error === 0) {
                            Swal.fire('Deleted!', Message, 'success');
                            fetchScholarships();
                        } else {
                            Swal.fire('Error!', Message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseText || xhr.statusText, 'error');
                    }
                });
            }
        });
    });


    // SCHOLAR AJAX

    // add Scholar View | DONE
    $(document).on("click", ".addScholarView", function(e) {
        e.preventDefault();
        let scholarshipId = $(this).data('scholarship-id');
        let scholarshipName = $(this).data('scholarship-name');

        window.location.href = "{{ route('add-scholar-view') }}?id=" + scholarshipId + "&name=" +
            encodeURIComponent(scholarshipName);
    });


    // array to store selected students 
    let selectedStudents = [];

    // disable ang search input sa student sa wala pa ma select ang school year ug semester
    $('#searchStudent').prop('disabled', true);

    // ma enable ang search Student input if naa nay sulod ang school year ug semester
    $('#schoolYear, #semester').on('change', function() {
        if ($('#schoolYear').val() && $('#semester').val()) {
            $('#searchStudent').prop('disabled', false);
        } else {
            $('#searchStudent').prop('disabled', true);
        }
    });

    // search student input
    $('#searchStudent').on('input', function() {
        let query = $(this).val().trim();
        let schoolYear = $('#schoolYear').val();
        let semester = $('#semester').val();

        if (query.length > 1 && schoolYear && semester) {
            $.ajax({
                url: "{{ route('search-student') }}",
                type: "GET",
                data: {
                    query: query,
                    scholarship_id: $('#scholarship_id').val(),
                    school_year: schoolYear,
                    semester: semester
                },
                success: function(data) {
                    let dropdown = $('#studentResults');
                    dropdown.empty().show();

                    if (data.length > 0) {
                        $.each(data, function(index, student) {

                            let fullName =
                                `${student.LastName}, ${student.FirstName} ${student.MiddleName}`;

                            let alreadyExistsText = student.alreadyExists ?
                                ' <span class="badge bg-warning ms-2"><i class="fa fa-exclamation-circle me-1"></i>Already Exists</span>' :
                                '';

                            dropdown.append(`
                                    <div class="list-group-item student-item ${student.alreadyExists ? 'disabled' : ''}" 
                                         data-id="${student.StudentNo}" 
                                         data-name="${fullName}" 
                                         style="cursor: pointer; padding: 10px; border-bottom: 1px solid #ddd;">
                                        ${fullName} (${student.StudentNo})${alreadyExistsText}
                                    </div>
                                `);
                        });

                        // Add hover effect
                        $('.student-item').hover(
                            function() {
                                $(this).css({
                                    'background-color': '#033874',
                                    'box-shadow': '0 4px 8px rgba(0, 0, 0, 0.2)',
                                    'cursor': 'pointer',
                                    'color': 'white'
                                });
                            },
                            function() {
                                $(this).css({
                                    'background-color': '',
                                    'box-shadow': '',
                                    'color': ''
                                });
                            }
                        );
                    } else {
                        dropdown.append(
                            '<div class="list-group-item">No results found</div>');
                    }
                },
                error: function(xhr) {
                    showToast('Error fetching students: ' + xhr.statusText, 'danger');
                }
            });
        } else {
            $('#studentResults').empty().hide();
        }
    });

    // mo handle sa student selection
    $(document).on('click', '.student-item:not(.disabled)', function() {
        let studentNo = $(this).data('id');
        let studentName = $(this).data('name');

        if (selectedStudents.some(s => s.studentNo === studentNo)) {
            showToast('Student is already selected.', 'warning');
            return;
        }


        selectedStudents.push({
            studentNo,
            studentName
        });

        // append to selected students
        $('#selectedStudents').append(`
            <div class="selected-student d-flex justify-content-between align-items-center p-2 border rounded mt-1">
                <span>${studentName} (${studentNo})</span>
                <button type="button" class="btn btn-sm btn-danger remove-student" data-id="${studentNo}">×</button>
            </div>
        `);

        // Clear search field & hide dropdown
        $('#searchStudent').val('');
        $('#studentResults').empty().hide();
    });

    // remove ang student sa selected students
    $(document).on('click', '.remove-student', function() {
        // nag initialize sa studentNo
        let studentNo = $(this).data('id');

        // i remove ang student sa selected students
        selectedStudents = selectedStudents.filter(s => s.studentNo !== studentNo);

        // i remove sa UI
        $(this).closest('.selected-student').remove();
    });




    // search a scholar
    $('#searchScholar').on('input', function() {
        let query = $(this).val().trim();
        let scholarshipId = $('#scholarship_id').val();

        $.ajax({
            url: "/scholarship-new/search-scholar",
            type: "GET",
            data: {
                query: query,
                scholarship_id: scholarshipId,
                is_search: true
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

    // add scholar
    $('#frmAddScholar').on('submit', function(e) {
        e.preventDefault();

        $(".toast").remove();

        if ($('#schoolYear').val() === null) {
            showToast('Please select a school year.', 'danger');
            return;
        }

        if ($('#semester').val() === null) {
            showToast('Please select a semester.', 'danger');
            return;
        }

        if (selectedStudents.length === 0) {
            showToast('Please select at least one student.', 'danger');
            return;
        }

        $.ajax({
            url: "/scholarship-new/add-scholar",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                scholarship_id: $('#scholarship_id').val(),
                students: selectedStudents.map(s => s.studentNo),
                schoolYear: $('#schoolYear').val(),
                semester: $('#semester').val()
            },
            success: function(response) {
                const {
                    Error,
                    Message
                } = response;

                if (Error === 0) {
                    showToast(Message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(Message, 'danger');
                }
            },
            error: function(xhr) {
                showToast('An error occurred while adding scholar: ' + xhr.statusText, 'danger');
            }
        });
    });

    // edit Scholar
    $(document).on("click", ".editScholar", function(e) {
        e.preventDefault();

        let scholarId = $(this).data('scholar-id');

        $.ajax({
            url: `/scholarship-new/edit-scholar/${scholarId}`,
            method: 'GET',
            success: function(response) {
                if (response.Error === 0) {
                    let scholar = response.Scholar;

                    $('#id').val(scholarId);
                    $('#editStudentNo').text(scholar.student_no);
                    $('#editStudentName').text(scholar.student_name);
                    $('#editSchoolYear').text(scholar.schoolYear);
                    $('#editSemester').text(scholar.semester);
                    $('#editDateAwarded').text(scholar.date_awarded);
                    $('#editBankAccount').text(scholar.bank_account);

                    $('#offcanvasEditScholar').offcanvas('show');
                } else {
                    showToast(response.Message, 'danger');
                }
            },
            error: function(xhr) {
                showToast('Error fetching scholarship: ' + xhr.statusText, 'danger');
            }
        });
    });

    // Update Scholar
    $(document).on("click", "#btnUpdateScholar", function(e) {
        e.preventDefault();

        let formData = $("#frmUpdateScholar").serialize();

        $.ajax({
            url: "{{ route('update-scholar') }}",
            method: 'PUT',
            data: formData,
            beforeSend: function() {
                $("#updateMsg").html('');
            },
            success: function(response) {
                const {
                    Error,
                    Message
                } = response;

                if (Error === 0) {
                    showToast(Message, 'success');
                    setTimeout(() => {
                        $('#offcanvasUpdateScholar').offcanvas('hide');
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(Message, 'danger');
                }
            },
            error: function(xhr) {
                showToast('An error occurred while updating scholar: ' + xhr.statusText, 'danger');
            }
        });
    });
    // delete scholarship | DONE
    $(document).on("click", ".deleteScholarship", function(e) {
        e.preventDefault();

        let scholarshipID = $(this).data('scholarship-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('delete-scholarship') }}",
                    method: 'DELETE',
                    data: {
                        id: scholarshipID
                    },
                    success: function(response) {
                        const {
                            Error,
                            Message
                        } = response;

                        if (Error === 0) {
                            Swal.fire('Deleted!', Message, 'success');
                            fetchScholarships();
                        } else {
                            Swal.fire('Error!', Message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseText || xhr.statusText, 'error');
                    }
                });
            }
        });
    });

    // delete scholar
    $(document).on("click", ".deleteScholar", function(e) {
        e.preventDefault();

        let scholarId = $(this).data('scholar-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('delete-scholar') }}",
                    method: 'DELETE',
                    data: {
                        id: scholarId
                    },
                    success: function(response) {
                        const {
                            Error,
                            Message
                        } = response;

                        if (Error === 0) {
                            Swal.fire('Deleted!', Message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error!', Message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseText || xhr.statusText, 'error');
                    }
                });
            }
        });
    });

    // Function to show toast
    function showToast(message, type) {
        let bgColorClass = '';

        switch (type) {
            case 'success':
                bgColorClass = 'bg-success';
                break;
            case 'danger':
                bgColorClass = 'bg-danger';
                break;
            case 'warning':
                bgColorClass = 'bg-warning';
                break;
            default:
                bgColorClass = 'bg-secondary';
        }

        let toast = `
        <div class="toast align-items-center text-white ${bgColorClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;

        $("#toastContainerCreate").html(toast);
        $("#toastContainerUpdate").html(toast);

        $('.toast').toast('show');
    }
</script>
