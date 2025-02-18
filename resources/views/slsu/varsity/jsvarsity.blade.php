<script>
    $.ajaxSetup({
        headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

$(document).ready(function() {
    let studentData = []; // Store student data globally

    // Fetch Student When Campus is Selected
    $(document).on("change", "#Campus", function(e) {
        let id = $("#Campus").val();
        e.preventDefault();

        $.ajax({
            url: "/varsity/student-campus",
            method: 'post',
            cache: false,
            data: { id },
            beforeSend: function() {
                $("#StudentList").empty();
                $("#Stud").val("");
                $("#StudentDropdown").hide();
            },
            success: function(data) {
                studentData = data; // Store fetched data for filtering
            },
            error: function(response) {
                var errors = response.responseJSON.errors;
                $("#StudentList").empty().append('<li class="list-group-item text-red">' + errors + '</li>');
                $("#StudentDropdown").show();
            }
        });
    });

    // Filter Student Based on Input Text
    $(document).on("input", "#Stud", function() {
        let searchValue = $(this).val().toLowerCase();
        let filteredStudent = studentData.filter(emp =>
            (emp.LastName + ", " + emp.FirstName + " " + (emp.MiddleName || "")).toLowerCase().includes(searchValue)
        );

        if (filteredStudent.length > 0) {
            populateStudentDropdown(filteredStudent);
            $("#StudentDropdown").show();
        } else {
            $("#StudentDropdown").hide();
        }
    });

    // Populate Student Dropdown List
    function populateStudentDropdown(data) {
        $("#StudentList").empty();
        $.each(data, function(i, item) {
            let middleInitial = item.MiddleName && item.MiddleName.length > 0 ? item.MiddleName : "";

            $("#StudentList").append(
                '<li class="list-group-item student-item" data-id="' + item.id + '">' +
                '<a href="#" class="text-decoration-none text-dark d-block p-2">' +
                item.LastName + ', ' + item.FirstName + (middleInitial ? ', ' + middleInitial : '') +
                '</a></li>'
            );
        });
    }

    // Auto-fill Input When an Student is Selected
    $(document).on("click", ".student-item", function(e) {
        e.preventDefault();
        let selectedText = $(this).text();
        $("#Stud").val(selectedText);
        $("#StudentDropdown").hide();
    });

    // Hide Dropdown When Clicking Outside
    $(document).click(function(e) {
        if (!$(e.target).closest("#Stud, #StudentDropdown").length) {
            $("#StudentDropdown").hide();
        }
    });
});


$(document).ready(function () {
    fetchEvents();
});

$('#search').on('input', function () {
    var query = $(this).val();

    $.ajax({
        url: '{{ route("search-varsity") }}',
        method: 'GET',
        data: { search: query },
        success: function (response) {
            setTimeout(function () {
                $('tbody').html(response.html);
            }, 1000);
        }
    });
});


$(document).on("click", "#btn-save", function(e){
    e.preventDefault();
    $.ajax({
      url: '/varsity/save-varsity',
      method: 'post',
      data: $("#frmAdd").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btn-save").prop("disabled", true);
          $("#btn-save").html("<i class = 'spinner-grow spinner-grow-sm'></i> Adding...");
          $("#msg").html("");
          $(".modal-dialog").removeClass("border border-danger rounded"); // Add red border on error
          $(".form-control, .form-select").removeClass("border border-danger"); // Remove error borders from inputs
      },
      success:function(data){

          $("#btn-save").prop("disabled", false);
          $("#btn-save").html("Add to list");

          if (data.Error == 1){
              $("#msg").html(data.Message);
              $(".modal-dialog").addClass("border border-danger rounded"); // Add red border on error

                // Highlight specific input/select fields with errors
                if (data.Message.includes("Invalid Campus")) {
                    $("#Campus").addClass("border border-danger");
                }
                if (data.Message.includes("Please select student")) {
                    $("#Stud").addClass("border border-danger");
                }
                if (data.Message.includes("Please select event")) {
                    $("#Event").addClass("border border-danger");
                }
                if (data.Message.includes("Please select school year")) {
                    $("#SchoolYear").addClass("border border-danger");
                }
                if (data.Message.includes("Please select semester")) {
                    $("#Semester").addClass("border border-danger");
                }
          }

          if (data.Error == 0){
            $("#msg").html(data.Message);
            setTimeout(function() {
             $(".all").hide();  
            $("#Campus").val(0);
              $('input[name="Campus"]').focus();
            }, 1000);

        }
          // $("#outAjax").html(data);

      }

    });
});


// Function to fetch all events
function fetchEvents() {
    $.ajax({
        url: "/varsity/student-event-list",
        method: "POST",
        beforeSend: function () {
            $("#Event").html('<option value="0">Loading...</option>');
        },
        success: function (response) {
            $("#Event").empty().append('<option value="0"></option>');

            if (response.length > 0) {
                $.each(response, function (i, item) {
                    $("#Event").append(
                        `<option value="${item.id}">${item.event}</option>`
                    );
                });
            } else {
                $("#Event").append('<option value="0">No events available</option>');
            }
        },
        error: function (xhr) {
            let errorMsg = xhr.responseJSON?.error || "An error occurred.";
            $("#Event").html('<option value="0">' + errorMsg + '</option>');
        }
    });

    $(document).ready(function () {
    $(".all").hide();

    $("#Campus").on("change", function () {
        let campusValue = $(this).val();

        if (campusValue !== "0") {
            $(".all").fadeIn(); // Show all elements when a campus is selected
        } else {
            $(".all").fadeOut(); // Hide all elements if "Select Campus" is chosen
        }
    });
});
}

function fetchEventsUP(selectedEventId = null) {
    $.ajax({
        url: "/varsity/event-list",
        method: "POST",
        beforeSend: function () {
            $("#updateEvent").html('<option value="0">Loading...</option>');
        },
        success: function (response) {
            $("#updateEvent").empty().append('<option value="0"></option>');

            if (response.length > 0) {
                $.each(response, function (i, item) {
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
        error: function (xhr) {
            let errorMsg = xhr.responseJSON?.error || "An error occurred.";
            $("#updateEvent").html(`<option value="0">${errorMsg}</option>`);
        },
    });
}


$(document).on("click", ".deleteVarsity", function(e){
    e.preventDefault();
    let id = $(this).attr("cid");
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
          data: {id},
          beforeSend:function(){
            Swal.fire({
              position: "center",
              icon: "info",
              title: "Deleting...",
              showConfirmButton: false
            });
          },
          success:function(data){
            window.location.reload();
          },
          error: function (response) {

            if (response.status == 419){
                window.location.reload();
            }else{
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

$(document).on("click", ".editVarsity", function (e) {
    e.preventDefault();
    let id = $(this).attr('cid');

    window.history.pushState({}, "", `/varsity/edit-varsity/${id}`);

    $.ajax({
        url: `/varsity/edit-varsity/${id}`,
        method: 'GET',
        success: function (data) {
            $("#hiddentID").val(id);
            $('#updateCampus').val(data.campus);
            $('#updateStud').val(data.stud);
            fetchEventsUP(data.event);
            $('#updateSY').val(data.sy);
            $('#updateSem').val(data.sem);
            $("#updateModalVar").modal('toggle');
        },
        error: function (response) {
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


$(document).on("click", "#btn-update", function(e){
    e.preventDefault();
    // let id = $("#updateHiddentID").val();
    $.ajax({
      url: "{{ route('update-varsity') }}",
      method: 'post',
      data: $("#frmUpdate").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btn-update").prop("disabled", true);
          $("#btn-update").html("<i class = 'spinner-grow spinner-grow-sm'></i> Updating...");
          $("#msg").html("");
          $(".modal-dialog").removeClass("border border-danger rounded"); // Add red border on error

      },
      success:function(data){

          $("#btn-update").prop("disabled", false);
          $("#btn-update").html("Update the list");

          if (data.Error == 1){
              $("#updatemsg").html(data.Message);
              $(".modal-dialog").addClass("border border-danger rounded"); // Add red border on error
          }

          if (data.Error == 0){
            $("#updatemsg").html(data.Message);
            setTimeout(function() {
                $("#updateCampus").val("");
                $("#updateStud").val("");
                $("#updateEvent").val(0);
                $("#updateSY").val(0);
                $("#updateSem").val(0);
            }, 1000);

        }
          // $("#outAjax").html(data);

      }

    });
});


$(document).on("hidden.bs.modal", "#modalVarsity", function(){
    $(".all").hide();
    $("#Campus").val(0);
    $("#Stud").val("");
    $("#Event").val(0);
    $("#SchoolYear").val(0);
    $("#Semester").val(0);
    window.location.reload();
});

$(document).on("hidden.bs.modal", "#updateModalVar" , function(){
    window.history.pushState({}, "", "/varsity/varsity");
    window.location.reload();
});


    </script>