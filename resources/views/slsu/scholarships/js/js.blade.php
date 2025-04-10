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

        // handle pagination
        $(document).on('change', '#entriesPerPage', function() {
            let entries = $(this).val();
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('entriesPerPage', entries);
            window.location.href = currentUrl.toString();
        });



        // scholarship UI for storing scholarships

        // show modal
        $('#createScholarshipModal').on('show.bs.modal', function() {
            $('#frmAddScholarship')[0].reset(); // reset form

            // clear validation states and feedback messages
            $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            $('.invalid-feedback, .valid-feedback').remove();

            // hide some inputs
            $('#scholarshipName').closest('.col').hide();
            $('#schAcronym').closest('.col').hide();
            $('#schProvider').closest('.mb-3').hide();
            $('#requirementsContainer').closest('.mb-3').hide();

            // hide external type options
            $('#externalOptions').hide();
            $('#externalScholarshipType').val('');

            // reset other fields
            $('#schProvider').prop('disabled', false);
            $('#schProviderHidden').val('');
            $('#scholarshipType').val('');
        });

        // handle scholarship type change
        $('#scholarshipType').on('change', function() {
            const scholarshipType = $(this).val();

            // reset validation states and feedback messages
            $('#schProvider, #externalScholarshipType').removeClass('is-valid is-invalid');
            $('#schProvider').next('.invalid-feedback, .valid-feedback').remove();
            $('#externalScholarshipType').next('.invalid-feedback, .valid-feedback').remove();

            // show scholarship name and acronym fields
            $('#scholarshipName').closest('.col').show();
            $('#schAcronym').closest('.col').show();

            // show or hide external options based on scholarship type
            if (scholarshipType === '1') {
                $('#externalOptions').hide();
                $('#externalScholarshipType').val('');
                $('#schProvider').val('SLSU');
                $('#schProviderHidden').val('SLSU');
                $('#schProvider').prop('disabled', true);
                $('#schProvider').closest('.mb-3').show();
            } else if (scholarshipType === '2') {
                $('#externalOptions').show();
                $('#schProvider').val('');
                $('#schProviderHidden').val('');
                $('#schProvider').prop('disabled', false);
                $('#schProvider').closest('.mb-3').show();
            }
        });

        // sync hidden input with provider field
        $('#schProvider').on('input change', function() {
            $('#schProviderHidden').val($(this).val());
        });

        // validation for inputs
        $('#scholarshipName, #schAcronym, #scholarshipType, #externalScholarshipType, #schProvider').on(
            'input change',
            function() {
                validateField($(this));
            });

        // validation function store scholarship
        function validateField(field) {
            const value = field.val().trim();
            const id = field.attr('id');

            // clear previoues validation states and feedback messages
            field.removeClass('is-valid is-invalid');
            field.next('.valid-feedback, .invalid-feedback').remove();

            // logic for validation
            if (id === 'scholarshipName' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Scholarship Name is required.</div>');
                return false;
            }

            if (id === 'schAcronym' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Scholarship Acronym is required.</div>');
                return false;
            }

            if (id === 'scholarshipType' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Please select a Scholarship Type.</div>');
                return false;
            }

            if (id === 'externalScholarshipType' && $('#scholarshipType').val() === '2' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Please select an External Type.</div>');
                return false;
            }

            if (id === 'schProvider' && $('#scholarshipType').val() === '2' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Scholarship Provider is required.</div>');
                return false;
            }

            // if validation passes
            field.addClass('is-valid');
            field.after('<div class="valid-feedback">Looks good!</div>');
            return true;
        }


        // scholarship UI for editing/updating scholarships





        // SCHOLARSHIP CRUD

        // store scholarship
        $('#btnStoreScholarship').on('click', function(e) {
            e.preventDefault();

            // clear previous validation states and feedback messages
            $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            $('.invalid-feedback').remove();

            let isValid = true;

            // validate scholarship name
            const scholarshipName = $('#scholarshipName');
            if (!scholarshipName.val().trim()) {
                isValid = false;
                scholarshipName.addClass('is-invalid');
                scholarshipName.after(
                    '<div class="invalid-feedback">Scholarship Name is required.</div>');
            } else {
                scholarshipName.addClass('is-valid');
            }

            // validate scholarship acronym
            const scholarshipAcronym = $('#schAcronym');
            if (!scholarshipAcronym.val().trim()) {
                isValid = false;
                scholarshipAcronym.addClass('is-invalid');
                scholarshipAcronym.after(
                    '<div class="invalid-feedback">Scholarship Acronym is required.</div>');
            } else {
                scholarshipAcronym.addClass('is-valid');
            }

            // validate scholarship type
            const scholarshipType = $('#scholarshipType');
            if (!scholarshipType.val()) {
                isValid = false;
                scholarshipType.addClass('is-invalid');
                scholarshipType.after(
                    '<div class="invalid-feedback">Please select a Scholarship Type.</div>');
            } else {
                scholarshipType.addClass('is-valid');
            }

            // validate external scholarship type if applicable
            const externalScholarshipType = $('#externalScholarshipType');
            if (scholarshipType.val() === '2' && !externalScholarshipType.val()) {
                isValid = false;
                externalScholarshipType.addClass('is-invalid');
                externalScholarshipType.after(
                    '<div class="invalid-feedback">Please select an External Type.</div>');
            } else if (scholarshipType.val() === '2') {
                externalScholarshipType.addClass('is-valid');
            }

            // validate scholarship provider if applicable
            const scholarshipProvider = $('#schProvider');
            if (scholarshipType.val() === '2' && !scholarshipProvider.val().trim()) {
                isValid = false;
                scholarshipProvider.addClass('is-invalid');
                scholarshipProvider.after(
                    '<div class="invalid-feedback">Scholarship Provider is required.</div>');
            } else if (scholarshipType.val() === '2') {
                scholarshipProvider.addClass('is-valid');
            }

            // if all inputs are valid, proceed with AJAX request
            if (isValid) {
                const formData = $('#frmAddScholarship').serialize();

                $.ajax({
                    url: "{{ route('scholarships.store') }}",
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#btnStoreScholarship')
                            .prop('disabled', true)
                            .html(
                                "<i class='spinner-grow spinner-grow-sm'></i> Creating...");
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
                                $('#createScholarshipModal').modal('hide');
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
                        $('#btnStoreScholarship').prop('disabled', false).html(
                            'Save Scholarship');
                    }
                });
            }
        });

        // Edit scholarship
        $(document).on('click', '.editScholarship', function(e) {
            e.preventDefault();

            const scholarshipId = $(this).data('scholarship-id');

            $.ajax({
                url: "{{ route('scholarships.edit') }}",
                method: 'GET',
                data: {
                    id: scholarshipId,
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

                        // Reset the form
                        $('#frmEditScholarship')[0].reset();

                        // Clear validation states and feedback messages
                        $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
                        $('.invalid-feedback, .valid-feedback').remove();

                        $('#editScholarshipId').val(scholarship.id);
                        $('#editScholarshipName').val(scholarship.name);
                        $('#editSchAcronym').val(scholarship.acronym);
                        $('#editScholarshipType').val(scholarship.type).change();
                        $('#editExternalScholarshipType').val(scholarship.externalType)
                            .change();
                        $('#editSchProvider').val(scholarship.provider);
                        $('#editSchProviderHidden').val(scholarship.provider);


                        // clear or update the original data attributes
                        $('#editExternalScholarshipType').data('original-external-type',
                            scholarship.type === 2 ? scholarship.externalType : '');
                        $('#editSchProvider').data('original-external-provider', scholarship
                            .type === 2 ? scholarship.provider : '');
                        $('#editSchProvider').data('original-provider', scholarship.type ===
                            1 ? scholarship.provider : '');

                        handleScholarshipTypeChange(scholarship.type);

                        // Trigger validation for all fields
                        $('#editScholarshipName, #editSchAcronym, #editScholarshipType, #editExternalScholarshipType, #editSchProvider')
                            .each(function() {
                                validateEditField($(this));
                            });

                        // Show the modal
                        $('#editScholarshipModal').modal('show');
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
                        title: 'Error',
                        text: 'Unable to fetch scholarship details. Please try again later.',
                    });
                },
            });
        });

        function handleScholarshipTypeChange(scholarshipType) {
            // Reset validation states for external fields
            $('#editExternalScholarshipType, #editSchProvider').removeClass('is-valid is-invalid');
            $('#editExternalScholarshipType').next('.valid-feedback, .invalid-feedback').remove();
            $('#editSchProvider').next('.valid-feedback, .invalid-feedback').remove();

            if (parseInt(scholarshipType) === 1) {
                // Internal scholarship
                $('#editExternalOptions').hide();
                $('#editExternalScholarshipType').val('');
                $('#editSchProvider').val('');
                $('#editSchProviderHidden').val('');

                // Restore the original provider value for internal scholarships
                const originalProvider = $('#editSchProvider').data('original-provider') || 'SLSU';
                $('#editSchProvider').val(originalProvider);
                $('#editSchProviderHidden').val(originalProvider);
                $('#editSchProvider').prop('disabled', true);
            } else if (parseInt(scholarshipType) === 2) {
                // External scholarship
                $('#editExternalOptions').show();

                // Prefill external type and provider if available
                const externalType = $('#editExternalScholarshipType').data('original-external-type') || '';
                const externalProvider = $('#editSchProvider').data('original-external-provider') || '';

                $('#editExternalScholarshipType').val(externalType);
                $('#editSchProvider').val(externalProvider);
                $('#editSchProviderHidden').val(externalProvider);
                $('#editSchProvider').prop('disabled', false);

                // Trigger validation for external fields
                validateEditField($('#editExternalScholarshipType'));
                validateEditField($('#editSchProvider'));
            }
        }


        $(document).on('change', '#editScholarshipType', function() {
            const scholarshipType = $(this).val();
            handleScholarshipTypeChange(scholarshipType);
        });

        $('#editScholarshipName, #editSchAcronym, #editScholarshipType, #editExternalScholarshipType, #editSchProvider')
            .on(
                'input change',
                function() {
                    validateEditField($(this));
                }
            );

        function validateEditField(field) {
            const value = field.val();
            const id = field.attr('id');

            // Clear previous validation states and feedback messages
            field.removeClass('is-valid is-invalid');
            field.next('.valid-feedback, .invalid-feedback').remove();

            // Validation logic
            if (id === 'editScholarshipName' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Scholarship Name is required.</div>');
                return false;
            }

            if (id === 'editSchAcronym' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Scholarship Acronym is required.</div>');
                return false;
            }

            if (id === 'editScholarshipType' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Please select a Scholarship Type.</div>');
                return false;
            }

            if (id === 'editExternalScholarshipType' && $('#editScholarshipType').val() === '2' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Please select an External Type.</div>');
                return false;
            }

            if (id === 'editSchProvider' && $('#editScholarshipType').val() === '2' && !value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Scholarship Provider is required.</div>');
                return false;
            }

            // If validation passes
            field.addClass('is-valid');
            field.after('<div class="valid-feedback">Looks good!</div>');
            return true;
        }



        // update scholarship
        $('#btnUpdateScholarship').on('click', function(e) {
            e.preventDefault();

            $('#editSchProviderHidden').val($('#editSchProvider').val());

            // Clear previous validation states
            $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            $('.invalid-feedback').remove();

            let isValid = true;

            // Validate all required fields
            $('#editScholarshipName, #editSchAcronym, #editScholarshipType, #editExternalScholarshipType, #editSchProvider')
                .each(
                    function() {
                        if (!validateEditField($(this))) {
                            isValid = false;
                        }
                    }
                );

            if (isValid) {
                const formData = $('#frmEditScholarship').serialize();

                $.ajax({
                    url: "{{ route('scholarships.update') }}",
                    method: 'PUT',
                    data: formData,
                    beforeSend: function() {
                        $('#btnUpdateScholarship')
                            .prop('disabled', true)
                            .html(
                                "<i class='spinner-grow spinner-grow-sm'></i> Updating...");
                    },
                    success: function(response) {
                        const {
                            Error,
                            Message
                        } = response;

                        if (!Error) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Scholarship Updated!',
                                text: Message,
                                showConfirmButton: true,
                            }).then(() => {
                                $('#editScholarshipModal').modal('hide');
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: Message,
                                showConfirmButton: true,
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'An unexpected error occurred',
                            text: xhr.statusText,
                            showConfirmButton: true,
                        });
                    },
                    complete: function() {
                        $('#btnUpdateScholarship').prop('disabled', false).html(
                            'Update Scholarship');
                    },
                });
            }

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





        // SCHOLARSHIP REQUIREMENTS

        // show modal for adding requirements
        $(document).on('click', '.addRequirements', function(e) {
            e.preventDefault();

            const scholarshipId = $(this).data('scholarship-id');
            const scholarshipName = $(this).data('scholarship-name');
            const scholarshipAcronym = $(this).data('scholarship-acronym');

            $('#addRequirementsModalLabel').text(
                `Add Requirements for ${scholarshipName.trim()} (${scholarshipAcronym.trim()})`);

            $('#addRequirementsForm input[name="scholarship_id"]').val(scholarshipId);

            $('#requirementsContainer').html('');

            $('#addRequirementsModal').modal('show');
        });

        // add new requirement row
        $('#btnAddRequirement').on('click', function() {
            const requirementHtml = `
                <div class="input-group mb-2 requirement-item">
                    <input type="number" name="quantities[]" class="form-control ms-2" placeholder="Qty" min="1" style="width: 80px; flex: 0 0 auto;">
                    <input type="text" name="requirements[]" class="form-control ms-2" placeholder="Enter a requirement">
                    <button type="button" class="btn btn-danger btnRemoveRequirement ms-2">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            `;
            $('#requirementsContainer').append(requirementHtml);
        });

        // remove a requirement row
        $(document).on('click', '.btnRemoveRequirement', function() {
            $(this).closest('.requirement-item').remove();
        });

        // save requirements
        $('#btnSaveRequiremnts').on('click', function() {
            let hasValidInput = false;
            $('input[name="requirements[]"]').each(function() {
                if ($(this).val().trim() !== '') {
                    hasValidInput = true;
                }
            });

            if (!hasValidInput) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Requirements',
                    text: 'Please enter at least one valid requirement before saving.',
                    showConfirmButton: true
                });
                return;
            }

            const formData = $('#addRequirementsForm').serialize();

            $.ajax({
                url: "{{ route('scholarships.requirements.store') }}",
                method: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#btnSaveRequiremnts').prop('disabled', true).html(
                        "<i class='spinner-grow spinner-grow-sm'></i> Saving...");
                },
                success: function(response) {
                    const {
                        Error,
                        Message
                    } = response;

                    if (Error == 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: Message,
                            showConfirmButton: true
                        }).then(() => {
                            $('#addRequirementsModal').modal('hide');
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
                    $('#btnSaveRequiremnts').prop('disabled', false).html(
                        "Save Requirements");
                }
            });
        });

    });
</script>
