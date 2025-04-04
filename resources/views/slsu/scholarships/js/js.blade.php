<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let searchTimeout;


        // INDEX PAGE
        // search scholarship
        $("#searchScholarship").on('input', function() {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                fetchScholarships();
            }, 1000);
        });

        // fetch scholarships
        function fetchScholarships() {
            let searchQuery = $('#searchScholarship').val();

            $.ajax({
                url: "{{ route('scholarships.index') }}",
                method: 'GET',
                data: {
                    searchScholarship: searchQuery
                },
                beforeSend: function() {
                    $('#loadingIndicator').show();
                    $('#scholarshipTable').hide();
                },
                success: function(response) {
                    $('#scholarshipTable').html(response.scholarshipTable);
                },
                error: function(xhr) {
                    let errorMessage = 'An unexpected error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.Message) {
                        errorMessage = xhr.responseJSON.Message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        showConfirmButton: true
                    });
                },
                complete: function() {
                    $('#loadingIndicator').hide();
                    $('#scholarshipTable').show();
                }
            });
        }

        // filter scholarship
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('scholarships.index') }}",
                type: "GET",
                data: formData,
                beforeSend: function() {
                    $('#loadingIndicator').show();
                    $('#scholarshipTable').hide();
                    $('#filterBtnScholarships').prop('disabled', true).html(
                        '<i class="spinner-border spinner-border-sm"></i> Filtering...');
                },
                success: function(response) {
                    $('#scholarshipTable').html(response.scholarshipTable);
                },
                error: function(Xhr) {
                    console.error("An error occurred:", xhr.responseText);
                    alert("Failed to filter scholarships. Please try again.");
                },
                complete: function() {
                    $('#loadingIndicator').hide();
                    $('#scholarshipTable').show();
                    $('#filterBtnScholarships').prop('disabled', false).html(
                        '<i class="fa fa-filter"></i> Filter');
                    $('#filterScholarshipType').val('');
                    $('#filterExternalType').val('');
                }
            });
        });

        // Handle entries per page change
        $(document).on('change', '#entriesPerPage', function() {
            let entries = $(this).val();
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('entriesPerPage', entries);
            window.location.href = currentUrl.toString();
        });


        // OFF CANVAS FOR ADDING SCHOLARSHIP
        // dynamic select   
        $('#scholarshipType, #editScholarshipType').on('change', function() {
            const isAddForm = $(this).attr('id') === 'scholarshipType';
            const externalOptions = isAddForm ? $('#externalOptions') : $('#editExternalOptions');
            const scholarshipProvider = isAddForm ? $('#schProvider').closest('.mb-3') : $(
                '#editSchProvider').closest('.mb-3');

            if ($(this).val() === '2') { // Assuming '2' is the value for "External"
                externalOptions.show();
                scholarshipProvider.hide(); // Initially hide the provider field
            } else {
                externalOptions.hide().find('select').val('');
                scholarshipProvider.hide().find('input').val('');
            }
        });

        // Show Scholarship Provider when a valid External Scholarship Type is selected
        $('#externalScholarshipType, #editExternalScholarshipType').on('change', function() {
            const isAddForm = $(this).attr('id') === 'externalScholarshipType';
            const scholarshipProvider = isAddForm ? $('#schProvider').closest('.mb-3') : $(
                '#editSchProvider').closest('.mb-3');

            if ($(this).val()) { // Show if a valid option is selected
                scholarshipProvider.show();
            } else {
                scholarshipProvider.hide().find('input').val('');
            }
        });

        const requirementsContainer = $('#requirementsContainer');

        // Add new requirement field
        $('#btnAddRequirement').on('click', function() {
            const newRequirement = `
        <div class="input-group mb-2 requirement-item">
            <input type="number" name="SchRequirementQuantities[]" class="form-control quantity-input"
                placeholder="Qty" min="1" style="width: 80px; flex: 0 0 auto;">
            <input type="text" name="SchRequirements[]" class="form-control ms-2"
                placeholder="Enter a requirement">
            <button type="button" class="btn btn-danger btnRemoveRequirement ms-2">
                <i class="fa fa-trash"></i>
            </button>
        </div>`;
            requirementsContainer.append(newRequirement);
        });

        // Remove requirement field
        requirementsContainer.on('click', '.btnRemoveRequirement', function() {
            $(this).closest('.requirement-item').remove();
        });

        // store scholarship
        $('#btnStoreScholarship').on('click', function(e) {
            e.preventDefault();

            formData = $("#frmAddScholarship").serialize();

            $.ajax({
                url: "{{ route('scholarships.store') }}",
                method: 'POST',
                data: formData,
                beforeSend: function() {
                    $("#btnStoreScholarship")
                        .prop("disabled", true)
                        .html("<i class='spinner-grow spinner-grow-sm'></i> Saving...");
                },
                success: function(response) {
                    const {
                        Error,
                        Message
                    } = response;

                    if (!Error) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Scholarship Created!',
                            text: Message,
                            showConfirmButton: true
                        }).then(() => {
                            $('#offcanvasAddScholarship').offcanvas('hide');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
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
                    $("#btnStoreScholarship").prop("disabled", false).html(
                        "Save Scholarship");
                }
            });
        });

        // edit scholarship
        $(document).on('click', '.editScholarship', function(e) {
            e.preventDefault();

            let id = $(this).data('scholarship-id');

            $.ajax({
                url: "{{ route('scholarships.edit') }}",
                method: 'GET',
                data: {
                    id: id
                },
                beforeSend: function() {
                    $('#editScholarshipMsg').html("");
                },
                success: function(response) {
                    const {
                        Error,
                        Message,
                        Scholarship
                    } = response;

                    if (Error == 0) {
                        let scholarship = Scholarship;

                        $('#scholarshipId').val(scholarship.id);
                        $('#editScholarshipName').val(scholarship.name);
                        $('#editScholarshipType').val(scholarship.type).change();

                        if (scholarship.type == 2) {
                            $('#editExternalOptions').show();
                            $('#editExternalScholarshipType').val(scholarship.externalType)
                                .change();
                        } else {
                            $('#editExternalOptions').hide();
                            $('#editExternalScholarshipType').val('');
                        }

                        $('#offcanvasEditScholarship').offcanvas('show');
                    } else {
                        $('#editScholarshipMsg').html(
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

        // update scholarship
        $('#btnUpdateScholarship').on('click', function(e) {
            e.preventDefault();

            let formData = $("#frmEditScholarship").serialize();

            $.ajax({
                url: "{{ route('scholarships.update') }}",
                method: 'PUT',
                data: formData,
                cache: false,
                dataType: 'json',
                beforeSend: function() {
                    $("#btnUpdateScholarship")
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
                            title: 'Updated',
                            text: Message,
                            showConfirmButton: true
                        }).then(() => {
                            $('#offcanvasEditScholarship').offcanvas('hide');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: Message,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'An unexpected error occurred',
                        text: xhr.responseJSON?.Message || xhr.statusText,
                        showConfirmButton: true
                    });
                },
                complete: function() {
                    $("#btnUpdateScholarship").prop("disabled", false).html(
                        "Update Scholarship");
                }
            });
        });

        // destroy scholarship
        $(document).on('click', '.deleteScholarship', function(e) {
            e.preventDefault();

            let id = $(this).data('scholarship-id');

            Swal.fire({
                title: 'Delete Scholarship',
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
                        url: "{{ route('scholarships.destroy') }}",
                        method: 'DELETE',
                        data: {
                            id: id
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
    });
</script>
