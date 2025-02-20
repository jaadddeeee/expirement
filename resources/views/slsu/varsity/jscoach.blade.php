<script>
    $.ajaxSetup({
        headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

$(document).ready(function() {
    let employeeData = []; // Store employee data globally

    // Fetch Employees When Campus is Selected
    $(document).on("change", "#Campus", function(e) {
        let id = $("#Campus").val();
        e.preventDefault();

        $.ajax({
            url: "/varsity/employees-campus",
            method: 'post',
            cache: false,
            data: { id },
            beforeSend: function() {
                $("#EmployeeList").empty();
                $("#Emp").val("");
                $("#EmployeeDropdown").hide();
            },
            success: function(data) {
                employeeData = data; // Store fetched data for filtering
            },
            error: function(response) {
                var errors = response.responseJSON.errors;
                $("#EmployeeList").empty().append('<li class="list-group-item text-danger">' + errors + '</li>');
                $("#EmployeeDropdown").show();
            }
        });
    });

    // Filter Employees Based on Input Text
    $(document).on("input", "#Emp", function() {
        let searchValue = $(this).val().toLowerCase();
        let filteredEmployees = employeeData.filter(emp =>
            (emp.LastName + ", " + emp.FirstName + " " + (emp.MiddleName || "")).toLowerCase().includes(searchValue)
        );

        if (filteredEmployees.length > 0) {
            populateEmployeeDropdown(filteredEmployees);
            $("#EmployeeDropdown").show();
        } else {
            $("#EmployeeDropdown").hide();
        }
    });

    // Populate Employee Dropdown List
    function populateEmployeeDropdown(data) {
        $("#EmployeeList").empty();
        $.each(data, function(i, item) {
            let middleInitial = item.MiddleName && item.MiddleName.length > 0 ? item.MiddleName : "";

            $("#EmployeeList").append(
                '<li class="list-group-item employee-item" data-id="' + item.id + '">' +
                '<a href="#" class="text-decoration-none text-dark d-block p-2">' +
                item.LastName + ', ' + item.FirstName + ' ' + (middleInitial ? ', ' + middleInitial : '') +
                '</a></li>'
            );
        });
    }

    // Auto-fill Input When an Employee is Selected
    $(document).on("click", ".employee-item", function(e) {
        e.preventDefault();
        let selectedText = $(this).text();
        $("#Emp").val(selectedText);
        $("#EmployeeDropdown").hide();
    });

    // Hide Dropdown When Clicking Outside
    $(document).click(function(e) {
        if (!$(e.target).closest("#Emp, #EmployeeDropdown").length) {
            $("#EmployeeDropdown").hide();
        }
    });
});

$('#search').on('input', function () {
    var query = $(this).val();
    var filterCampus = $('#filterCampus').val();

    $.ajax({
        url: '{{ route("coaches") }}',
        method: 'GET',
        data: { search: query, filterCampus: filterCampus },
        success: function (response) {
            setTimeout(function () {
                $('#data').html(response.html);
                
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

$(document).ready(function () {
    fetchEvents();
});

$(document).on("click", "#btn-save", function(e){
    e.preventDefault();
    $.ajax({
      url: '/varsity/save-coach',
      method: 'post',
      data: $("#frmAdd").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btn-save").prop("disabled", true);
          $("#btn-save").html("<i class = 'spinner-grow spinner-grow-sm'></i> Adding...");
          $("#msg").html("");
          $(".modal-dialog").removeClass("border border-danger rounded"); // Add red border on error
      },
      success:function(data){

          $("#btn-save").prop("disabled", false);
          $("#btn-save").html("Add to list");

          if (data.Error == 1){
              $("#msg").html(data.Message);
              $(".modal-dialog").addClass("border border-danger rounded"); // Add red border on error
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
        url: "/varsity/event-list",
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


$(document).on("click", ".deleteCoach", function(e){
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
          url: '/varsity/delete-coach',
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

$(document).on("click",".editCoach", function(e){
  e.preventDefault();
  let id = $(this).attr('cid');

  window.history.pushState({}, "", `/varsity/edit-coach/${id}`);


  $.ajax({
      url: `/varsity/edit-coach/${id}`,
      method: 'GET',
      beforeSend:function(){
      },
      success:function(data){
          $("#hiddentID").val(id);
          $('#updateCampus').val(data.campus);  
          $('#updateEmp').val(data.emp);
          $('#updateCT').val(data.coachType);
          fetchEventsUP(data.event)
          $("#updateModalCoach").modal('toggle');
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
});


$(document).on("click", "#btn-update", function(e){
    e.preventDefault();
    // let id = $("#updateHiddentID").val();
    $.ajax({
      url: "{{ route('update-coach') }}",
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
                $("#updateEmp").val("");
                $("#updateEvent").val(0);
                $("#updateCT").val(0);
            }, 1000);

        }
          // $("#outAjax").html(data);

      }

    });
});


$(document).on("hidden.bs.modal", "#modalCoach", function(){
    $(".all").hide();
    $("#Campus").val(0);
    $("#Employee").val(0);
    $("#Event").val(0);
    $("#coachType").val(0);
    window.location.reload();
});

$(document).on("hidden.bs.modal", "#updateModalCoach" , function(){
    window.history.pushState({}, "", "/varsity/coach");
    window.location.reload();
});

    </script>