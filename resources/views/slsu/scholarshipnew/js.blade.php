<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on("click", "#btnSaveScholar", function(e) {
        e.preventDefault();
        $.ajax({
            url: '/scholarship-new/save-scholarship',
            method: 'POST',
            data: $("#frmAddScholarship").serialize(),
            cache: false,
            dataType: 'json',
            beforeSend: function() {
                $("#btnSaveScholar")
                    .prop("disabled", true)
                    .html("<i class='spinner-grow spinner-grow-sm'></i> Adding...");
                $("#msg").html("");
            },
            success: function(response) {
                const {
                    Error,
                    Message
                } = response;

                if (Error === 0) {
                    $("#msg").html(`<div class="alert alert-success">${Message}</div>`);
                    setTimeout(function() {
                        $('#offcanvasAddScholar').offcanvas('hide');
                        $("#msg").html('');
                        $.get('/scholarship-new/get-scholarships', function(data) {
                            $(".datatable tbody").html(data);
                        });
                    }, 1000);
                } else {
                    $("#msg").html(`<div class="alert alert-danger">${Message}</div>`);
                }
            },
            error: function(xhr) {
                $("#msg").html(
                    `<div class="alert alert-danger">An unexpected error occurred: ${xhr.statusText}</div>`
                );
            },
            complete: function() {
                $("#btnSaveScholar").prop("disabled", false).html("Save");
            }
        });
    });

    $('#scholarshipType').on('change', function() {
        const externalOptions = $('#externalOptions');
        if ($(this).val() == '2') {
            externalOptions.show();
        } else {
            externalOptions.hide();
            $('#externalScholarshipType').val('');
        }
    });

    $('#editScholarshipType').on('change', function() {
        if ($(this).val() == '2') {
            $('#editExternalOptions').show();
        } else {
            $('#editExternalOptions').hide();
            $('#editExternalScholarshipType').val('');
        }
    });

    // edit
    function editScholar(encryptedId) {
        let url = '{{ route('edit-scholarship', ':id') }}'.replace(':id', encryptedId);

        //window.history.pushState({}, '', url);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $("#editMsg").html(
                    "<div class='alert alert-info'><i class='spinner-grow spinner-grow-sm'></i> Loading...</div>"
                );
            },
            success: function(response) {
                if (response.Error === 0) {
                    const scholarship = response.Scholarship;

                    $("#editScholarshipId").val(encryptedId);
                    $("#editScholarshipName").val(scholarship.sch_name);
                    $("#editScholarshipType").val(scholarship.sch_type).trigger('change');
                    $("#editExternalScholarshipType").val(scholarship.ext_type);

                    $("#offcanvasEditScholar").offcanvas('show');
                } else {
                    $("#editMsg").html(`<div class="alert alert-danger">${response.Message}</div>`);
                }
            },
            error: function(xhr) {
                $("#editMsg").html(
                    `<div class="alert alert-danger">An unexpected error occurred: ${xhr.statusText}</div>`
                );
            }
        });
    }

    // update
    $(document).on("click", "#btnUpdateScholar", function(e) {
        e.preventDefault();
        const encryptedId = $("#editScholarshipId").val();

        $.ajax({
            url: `/scholarship-new/update-scholarship/${encryptedId}`,
            type: 'POST',
            data: $("#frmEditScholarship").serialize(),
            dataType: 'json',
            beforeSend: function() {
                $("#btnUpdateScholar").prop("disabled", true).html(
                    "<i class='spinner-grow spinner-grow-sm'></i> Updating...");
                $("#editMsg").html("");
            },
            success: function(response) {
                if (response.Error === 0) {
                    $("#editMsg").html(
                        `<div class="alert alert-success">${response.Message}</div>`);
                    setTimeout(function() {
                        $("#offcanvasEditScholar").offcanvas('hide');
                        $.get('/scholarship-new/get-scholarships', function(data) {
                            $(".datatable tbody").html(data);
                        });
                    }, 1000);
                } else {
                    $("#editMsg").html(`<div class="alert alert-danger">${response.Message}</div>`);
                }
            },
            error: function(xhr) {
                $("#editMsg").html(
                    `<div class="alert alert-danger">An unexpected error occurred: ${xhr.statusText}</div>`
                );
            },
            complete: function() {
                $("#btnUpdateScholar").prop("disabled", false).html("Update");
            }
        });
    });
</script>
