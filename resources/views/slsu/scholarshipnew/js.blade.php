<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // save scholarship
    $(document).on("click", "#btnSaveScholar", function(e) {
        e.preventDefault();
        $.ajax({
            url: '/scholarship-new/save-scholarship',
            method: 'POST',
            data: $("#frmAddScholarship").serialize(),
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
                        fetchScholarships();
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

    // fetch and populate data
    function editScholarship(id) {
        $.ajax({
            url: `/scholarship-new/edit-scholarship/${id}`,
            method: 'GET',
            beforeSend: function() {
                $('#editMsg').html('');
            },
            success: function(response) {
                if (response.Error === 0) {
                    let scholarship = response.Scholarship;
                    $('#editScholarshipId').val(scholarship.id);
                    $('#editScholarshipName').val(scholarship.name);
                    $('#editScholarshipType').val(scholarship.type).change();

                    if (scholarship.type == 2) {
                        $('#editExternalOptions').show();
                        $('#editExternalScholarshipType').val(scholarship.externalType);
                    } else {
                        $('#editExternalOptions').hide();
                    }

                    $('#offcanvasEditScholar').offcanvas('show');
                } else {
                    alert(response.Message);
                }
            },
            error: function(xhr) {
                alert('Error fetching scholarship: ' + xhr.statusText);
            }
        });
    }

    // update
    $(document).on("click", "#btnUpdateScholar", function(e) {
        e.preventDefault();

        let formData = $("#frmEditScholarship").serializeArray();

        $.ajax({
            url: '/scholarship-new/update-scholarship/' + $("#editScholarshipId").val(),
            method: 'PUT',
            data: $("#frmEditScholarship").serialize(),
            cache: false,
            dataType: 'json',
            beforeSend: function() {
                $("#btnUpdateScholar")
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
                    $("#editMsg").html(`<div class="alert alert-success">${Message}</div>`);
                    setTimeout(function() {
                        $('#offcanvasEditScholar').offcanvas('hide');
                        $("#editMsg").html('');
                        fetchScholarships();
                    }, 1000);
                } else {
                    $("#editMsg").html(`<div class="alert alert-danger">${Message}</div>`);
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

    // fetch data to the table after saving, updating and deleting record
    function fetchScholarships() {
        $.ajax({
            url: '/scholarship-new/fetch-scholarships',
            method: 'GET',
            success: function(data) {
                $(".datatable tbody").html(data);
            },
            error: function(xhr) {
                console.error(`Failed to fetch scholarships: ${xhr.statusText}`);
            }
        });
    }

    // dynamic select 
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

    // delete scholarship
    function deleteScholarship(encryptedId) {
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
                    url: `/scholarship-new/delete-scholarship/${encryptedId}`,
                    method: 'DELETE',
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
    }
</script>
