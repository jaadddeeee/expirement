<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let searchTimeout;

        // search scholar
        $("#searchScholar").on('input', function() {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                fetchScholars();
            }, 1000);
        });

        function fetchScholars() {
            let searchQuery = $('#searchScholar').val();
            let id = $('#scholarshipId').val();
            let scholarshipName = "{{ request('scholarshipName') }}";
            let schoolYear = $('#filterSchoolYear').val();
            let semester = $('#filterSemester').val();
            let entriesPerPage = $('#entriesPerPage').val();

            $.ajax({
                url: "{{ route('scholars.index') }}",
                method: 'GET',
                data: {
                    searchScholar: searchQuery,
                    id: id,
                    scholarshipName: scholarshipName,
                    filterSchoolYear: schoolYear,
                    filterSemester: semester,
                    entriesPerPage: entriesPerPage
                },
                beforeSend: function() {
                    $('#loadingIndicator').show();
                    $('#scholarsTable').hide();
                },
                success: function(response) {
                    $('#scholarsTable').html(response.scholarsTable);

                    // Reapply checked state to previously selected scholars
                    $('.select-scholar').each(function() {
                        const scholarId = $(this).val();
                        if (selectedScholarIds.includes(scholarId)) {
                            $(this).prop('checked', true).trigger('change');
                        }
                    });

                    initializeSelectAllFunctionality();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch scholars. Please try again.',
                        showConfirmButton: true
                    });
                },
                complete: function() {
                    $('#loadingIndicator').hide();
                    $('#scholarsTable').show();
                }
            });
        }

        function toggleAddScholarButton() {
            const schoolYear = $('#filterSchoolYear').val();
            const semester = $('#filterSemester').val();

            if (schoolYear && semester) {
                $('#addScholarBtnModal').removeClass('d-none');
            } else {
                $('#addScholarBtnModal').addClass('d-none');
            }
        }

        toggleAddScholarButton();

        // Handle filter form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize() + '&scholarshipName={{ request('scholarshipName') }}';

            $.ajax({
                url: "{{ route('scholars.index') }}",
                method: 'GET',
                data: formData,
                beforeSend: function() {
                    $('#loadingIndicator').show();
                    $('#scholarsTable').hide();
                    $('#filterBtnScholars').prop('disabled', true).html(
                        '<i class="spinner-border spinner-border-sm"></i> Filtering...'
                    );
                },
                success: function(response) {
                    $('#scholarsTable').html(response.scholarsTable);

                    toggleAddScholarButton();

                    let currentUrl = new URL(window.location.href);
                    let filterParams = new URLSearchParams(formData);
                    filterParams.forEach((value, key) => {
                        currentUrl.searchParams.set(key, value);
                    });
                    window.history.pushState({}, '', currentUrl.toString());

                    initializeSelectAllFunctionality();
                },
                error: function(xhr) {
                    console.error("An error occurred:", xhr.responseText);
                    alert("Failed to filter scholars. Please try again.");
                },
                complete: function() {
                    $('#loadingIndicator').hide();
                    $('#scholarsTable').show();
                    $('#filterBtnScholars').prop('disabled', false).html(
                        '<i class="fa fa-filter"></i> Filter'
                    );
                }
            });
        });

        // Handle entriesPerPage change
        $(document).on('change', '#entriesPerPage', function() {
            let entries = $(this).val();
            let currentUrl = new URL(window.location.href);

            let filterParams = $('#filterForm').serializeArray();
            filterParams.forEach(param => {
                currentUrl.searchParams.set(param.name, param.value);
            });

            currentUrl.searchParams.set('entriesPerPage', entries);
            currentUrl.searchParams.set('scholarshipName', "{{ request('scholarshipName') }}");

            window.location.href = currentUrl.toString();
        });

        // add a student to scholarship
        let selectedStudents = [];

        // search student
        $('#searchStudent').on('input', function() {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                let scholarshipId = $('#scholarship_id').val();
                let searchStudent = $('#searchStudent').val().trim();
                let schoolYear = $('#filterSchoolYear').val();
                let semester = $('#filterSemester').val();

                if (searchStudent.length > 1 && schoolYear && semester) {
                    $.ajax({
                        url: "{{ route('scholars.search-student') }}",
                        type: "GET",
                        data: {
                            searchStudent: searchStudent,
                            id: scholarshipId,
                            filterSchoolYear: schoolYear,
                            filterSemester: semester
                        },
                        success: function(data) {
                            let dropdown = $('#studentResults');
                            dropdown.empty().show();

                            if (data.length > 0) {
                                $.each(data, function(index, student) {
                                    let fullName =
                                        `${student.LastName}, ${student.FirstName} ${student.MiddleName}`;

                                    let alreadyExistsText = student
                                        .alreadyExists ?
                                        ' <span class="badge bg-warning ms-2"><i class="fa fa-exclamation-circle"></i></span>' :
                                        '';

                                    if (student.alreadyExists) {
                                        dropdown.append(`
                                    <div class="list-group-item student-item disabled" 
                                        data-id="${student.StudentNo}" 
                                        data-name="${fullName}" 
                                        style="cursor: pointer; padding: 10px; border-bottom: 1px solid #ddd;"> ${student.StudentNo} - ${fullName}<span class="float-end">${alreadyExistsText}</span>
                                    </div>
                                `);
                                    } else {
                                        dropdown.append(`
                                    <div class="list-group-item student-item" 
                                        data-id="${student.StudentNo}" 
                                        data-name="${fullName}" 
                                        style="cursor: pointer; padding: 10px; border-bottom: 1px solid #ddd;">
                                        ${student.StudentNo} - ${fullName}
                                    </div>
                                `);
                                    }
                                });

                                // Add hover effect
                                $('.student-item').hover(
                                    function() {
                                        $(this).css({
                                            'background-color': 'grey',
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
                                    '<div class="list-group-item">No results found</div>'
                                );
                            }
                        },
                        error: function(xhr) {
                            console.error('Error fetching students:', xhr);
                        }
                    });
                } else {
                    $('#studentResults').empty().hide();
                }
            }, 1000);
        });

        // handle student selection
        $(document).on('click', '.student-item:not(.disabled)', function() {
            let studentNo = $(this).data('id');
            let studentName = $(this).data('name');

            if (selectedStudents.some(s => s.studentNo === studentNo)) {
                $("#addScholarMsg").html(
                    `<div class="alert alert-warning alert-dismissible" role="alert"> Student is already selected.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             </div>`
                );

                setTimeout(function() {
                    $("#addScholarMsg .alert").alert('close');
                }, 5000);

                return;
            }

            selectedStudents.push({
                studentNo,
                studentName
            });

            // Append to selected students
            $('#selectedStudents').append(`
                <div class="selected-student d-flex justify-content-between align-items-center p-2 border rounded mt-1">
                    <span>${studentNo} - ${studentName} </span>
                    <button type="button" class="btn btn-sm btn-danger remove-student" data-id="${studentNo}">×</button>
                </div>
            `);

            // Clear search field & hide dropdown
            $('#searchStudent').val('');
            $('#studentResults').empty().hide();
        });

        // remove student from selected students
        $(document).on('click', '.remove-student', function() {
            let studentNo = $(this).data('id');

            selectedStudents = selectedStudents.filter(s => s.studentNo !== studentNo);

            $(this).closest('.selected-student').remove();
        });

        // Add a scholar (single/multiple students)
        $('#addScholarBtn').on('click', function(e) {
            e.preventDefault();

            const schoolYear = $('#filterSchoolYear').val();
            const semester = $('#filterSemester').val();

            // Validate that School Year and Semester are selected
            if (!schoolYear || !semester) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Filters',
                    text: 'Please select a School Year and Semester before adding scholars.',
                    showConfirmButton: true
                });
                return;
            }

            $.ajax({
                url: "{{ route('scholars.store') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    scholarship_id: $('#scholarship_id').val(),
                    students: selectedStudents.map(s => s.studentNo),
                    schoolYear: schoolYear,
                    semester: semester
                },
                beforeSend: function() {
                    $("#addScholarBtn")
                        .prop("disabled", true)
                        .html("<i class='spinner-grow spinner-grow-sm'></i> Adding...");
                },
                success: function(response) {
                    const {
                        Error,
                        Message
                    } = response;

                    if (Error == 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: Message,
                            showConfirmButton: true
                        }).then(() => {
                            $('#offcanvasAddScholar').offcanvas('hide');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning',
                            text: Message,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'An unexpected error occurred',
                        text: xhr.statusText,
                        showConfirmButton: true
                    });
                },
                complete: function() {
                    $("#addScholarBtn").prop("disabled", false).html("Add Scholar");
                }
            });
        });

        // edit scholar
        $(document).on('click', '.editScholar', function(e) {
            e.preventDefault();

            let scholarId = $(this).data('scholar-id');

            $.ajax({
                url: "{{ route('scholars.edit') }}",
                method: 'GET',
                data: {
                    id: scholarId
                },
                success: function(response) {
                    const {
                        Error,
                        Message,
                        Scholar
                    } = response;

                    if (Error == 0) {
                        let scholar = Scholar;

                        $('#id').val(scholar.id);
                        $('#displayStudentNo').text(scholar.student_no);
                        $('#displayStudentName').text(scholar.student_name);
                        $('#editAwardNo').val(scholar.award_no);
                        $('#editDateAwarded').val(scholar.date_awarded);
                        $('#editBankAccount').val(scholar.bank_account);

                        $('#offcanvasEditScholar').offcanvas('show');
                    } else {
                        $("#editScholarMsg").html(
                            `<div class="alert alert-danger alert-dismissible" role="alert">${Message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                </button>
                             </div>`
                        );
                    }
                },
                error: function(xhr) {
                    alert('Error fetching scholarship: ' + xhr.statusText);
                }
            });
        });

        // update scholar
        $('#btnUpdateScholar').on('click', function(e) {
            e.preventDefault();

            let formData = $("#frmUpdateScholar").serialize();

            $.ajax({
                url: "{{ route('scholars.update') }}",
                method: 'PUT',
                data: formData,
                cache: false,
                dataType: 'json',
                beforeSend: function() {
                    $("#btnUpdateScholar")
                        .prop("disabled", true)
                        .html("<i class='spinner-grow spinner-grow-sm'></i> Updating...");
                },
                success: function(response) {
                    const {
                        Error,
                        Message
                    } = response;

                    if (Error == 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: Message,
                            showConfirmButton: true
                        }).then(() => {
                            $('#offcanvasEditScholar').offcanvas('hide');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: Message,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'An unexpected error occurred',
                        text: xhr.statusText,
                        showConfirmButton: true
                    });
                },
                complete: function() {
                    $("#btnUpdateScholar").prop("disabled", false).html("Update Scholar");
                }
            });
        });

        // destroy scholar
        $(document).on('click', '.deleteScholar', function(e) {
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
                        url: "{{ route('scholars.destroy') }}",
                        method: 'DELETE',
                        data: {
                            id: scholarId,
                            _token: "{{ csrf_token() }}" // Add CSRF token for security
                        },
                        success: function(response) {
                            const {
                                Error,
                                Message
                            } = response;

                            if (Error == 0) {
                                Swal.fire('Deleted!', Message, 'success').then(
                                    () => {
                                        window.location.reload();
                                    });
                            } else {
                                Swal.fire('Error!', Message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseText || xhr.statusText,
                                'error');
                        }
                    });
                }
            });
        });


        let selectedScholarIds = [];

        function initializeSelectAllFunctionality() {
            // Select all checkbox event listener
            $('#selectAllScholars').off('change').on('change', function() {
                const isChecked = $(this).is(':checked');
                const schoolYearFrom = $('#filterSchoolYear').val();
                const semesterFrom = $('#filterSemester').val();

                if (!schoolYearFrom || !semesterFrom) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Filters',
                        text: 'Please select a school year and semester in the filters before selecting all scholars.',
                        confirmButtonText: 'OK',
                    }).then(() => {
                        $('#selectAllScholars').prop('checked', false);
                    });
                    return;
                }

                $('.select-scholar').each(function() {
                    const scholarId = $(this).val();

                    if (isChecked) {
                        if (!selectedScholarIds.includes(scholarId)) {
                            selectedScholarIds.push(scholarId);
                        }
                    } else {
                        selectedScholarIds = selectedScholarIds.filter(id => id !== scholarId);
                    }

                    $(this).prop('checked', isChecked).trigger('change');
                });

                updateSelectAllCheckboxState();
                updateDeleteButton();
                updateCopyButton();
            });

            // Individual scholar checkbox event listener
            $(document).off('change', '.select-scholar').on('change', '.select-scholar', function() {
                const scholarId = $(this).val();
                const row = $(this).closest('tr');

                if ($(this).is(':checked')) {
                    if (!selectedScholarIds.includes(scholarId)) {
                        selectedScholarIds.push(scholarId);
                    }
                    row.addClass('highlight');
                } else {
                    selectedScholarIds = selectedScholarIds.filter(id => id !== scholarId);
                    row.removeClass('highlight');
                }

                updateSelectAllCheckboxState();
                updateDeleteButton();
                updateCopyButton();
            });

            // Update the state of the "Select All" checkbox and buttons
            updateSelectAllCheckboxState();
            updateDeleteButton();
            updateCopyButton();
        }

        function updateSelectAllCheckboxState() {
            const totalCheckboxes = $('.select-scholar').length;
            const checkedCheckboxes = $('.select-scholar:checked').length;

            const selectAllCheckbox = $('#selectAllScholars')[0];

            if (!selectAllCheckbox) {
                return; // Prevent error if checkbox is not on the page
            }

            if (checkedCheckboxes === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCheckboxes === totalCheckboxes) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        function updateCopyButton() {
            const count = $('.select-scholar:checked').length;
            const copyButton = $('#copyScholarsButton');
            const copyCountBadge = $('#copyCountBadge');

            if (count > 0) {
                copyCountBadge.text(count);
                copyButton.show();
            } else {
                copyCountBadge.text('');
                copyButton.hide();
            }
        }

        function updateDeleteButton() {
            const count = $('.select-scholar:checked').length;
            const deleteButton = $('#deleteScholarsBtn');
            const deleteCountBadge = $('#deleteCountBadge');

            if (count > 0) {
                deleteCountBadge.text(count);
                deleteButton.show();
            } else {
                deleteCountBadge.text('');
                deleteButton.hide();
            }
        }

        initializeSelectAllFunctionality();

        $('#copyScholarsButton').on('click', function() {
            // Get the selected school year and semester from the filters
            const schoolYearFrom = $('#filterSchoolYear').val();
            const semesterFrom = $('#filterSemester').val();

            // Update the hidden inputs in the modal
            $('#hiddenSchoolYearFrom').val(schoolYearFrom);
            $('#hiddenSemesterFrom').val(semesterFrom);

            // Update the displayed "From" information in the modal
            $('#displaySchoolYearFrom').text(schoolYearFrom || 'Not Set');
            $('#displaySemesterFrom').text(
                semesterFrom ?
                $('#filterSemester option:selected').text() :
                'Not Set'
            );

            // Add a warning class if the values are not set
            $('#displaySchoolYearFrom').toggleClass('text-danger', !schoolYearFrom);
            $('#displaySemesterFrom').toggleClass('text-danger', !semesterFrom);
        });

        // copy scholars btn
        $('#saveCopyChanges').on('click', function() {
            const scholarshipId = $('input[name="scholarship_id"]').val();
            const schoolYearFrom = $('#hiddenSchoolYearFrom').val();
            const semesterFrom = $('#hiddenSemesterFrom').val();
            const schoolYearTo = $('#schoolYearTo').val();
            const semesterTo = $('#semesterTo').val();
            const selectedScholars = $('.select-scholar:checked').map(function() {
                return $(this).val();
            }).get();

            if (!schoolYearFrom || !semesterFrom || !schoolYearTo || !semesterTo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'Please fill in all required fields.',
                });
                return;
            }

            if (schoolYearFrom === schoolYearTo && semesterFrom === semesterTo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Selection',
                    text: 'Source and target school year and semester must be different.',
                });
                return;
            }

            if (selectedScholars.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Scholars Selected',
                    text: 'Please select at least one scholar to copy.',
                });
                return;
            }

            // Confirm the action
            Swal.fire({
                title: 'Copy Scholars',
                text: `You are about to copy ${selectedScholars.length} selected scholars to the target school year and semester.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, copy them!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('scholars.copy-scholars') }}",
                        method: 'POST',
                        data: {
                            scholarship_id: scholarshipId,
                            schoolYearFrom: schoolYearFrom,
                            semesterFrom: semesterFrom,
                            schoolYearTo: schoolYearTo,
                            semesterTo: semesterTo,
                            selected_scholars: selectedScholars,
                            _token: "{{ csrf_token() }}",
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Please wait while we copy the scholars.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.success,
                                }).then(() => {
                                    location.reload();
                                });
                            } else if (response.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.error,
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'An Error Occurred',
                                text: xhr.responseJSON?.error ||
                                    'Failed to copy scholars. Please try again.',
                            });
                        },
                    });
                }
            });
        });

        // delete scholars
        $('#deleteScholarsBtn').on('click', function() {
            const selectedScholars = $('.select-scholar:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedScholars.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Scholars Selected',
                    text: 'Please select at least one scholar to delete.',
                });
                return;
            }

            Swal.fire({
                title: 'Delete Scholars',
                text: `You are about to delete ${selectedScholars.length} selected scholars. This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('scholars.delete-scholars') }}",
                        method: 'DELETE',
                        data: {
                            scholars: selectedScholars,
                            _token: "{{ csrf_token() }}",
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Please wait while we delete the scholars.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });
                        },
                        success: function(response) {
                            if (response.Error === 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.Message,
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.Message,
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'An Error Occurred',
                                text: xhr.responseJSON?.Message ||
                                    'Failed to delete scholars. Please try again.',
                            });
                        },
                    });
                }
            });
        });

        // Function to get the long format of the semester
        function getSemesterLongFormat(semester) {
            const semesters = {
                1: "First Semester",
                2: "Second Semester",
                9: "Summer",
                10: "Summer 2"
            };
            return semesters[semester] || semester;
        }

        // generate PDF Scholarship Certificate
        $(document).on('click', '.generateSCHCert', function() {
            let scholarId = $(this).data('scholar-id');
            let enrollmentId = $(this).data('enrollment-id');
            let schoolYear = $(this).data('school-year');
            let semester = $(this).data('semester');

            let url = "{{ route('generate-noa') }}" +
                "?scholar_id=" + encodeURIComponent(scholarId) +
                "&enrollment_id=" + encodeURIComponent(enrollmentId) +
                "&school_year=" + encodeURIComponent(schoolYear) +
                "&semester=" + encodeURIComponent(semester);

            // Open the generated certificate in a new tab (forces the download)
            window.open(url, '_blank');
        });

        // generate PDF Scholarship Profile Form
        $(document).on('click', '.generateSCHProfileForm', function() {
            let scholarId = $(this).data('scholar-id');
            let enrollmentId = $(this).data('enrollment-id');
            let schoolYear = $(this).data('school-year');
            let semester = $(this).data('semester');

            let url = "{{ route('generate-profile-form') }}" +
                "?scholar_id=" + encodeURIComponent(scholarId) +
                "&enrollment_id=" + encodeURIComponent(enrollmentId) +
                "&school_year=" + encodeURIComponent(schoolYear) +
                "&semester=" + encodeURIComponent(semester);

            // Open the generated certificate in a new tab (forces the download)
            window.open(url, '_blank');
        });
    });
</script>
