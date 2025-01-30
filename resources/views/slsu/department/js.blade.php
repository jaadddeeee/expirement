<script>
  $.ajaxSetup({
    headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});


$(document).on("click", ".deleteemp", function(e){
    e.preventDefault();
    let id = $(this).attr("eid");
    Swal.fire({
      title: "Are you sure?",
      text: "This will delete the employee select. You can't revert this.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Delete",
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        $.ajax({
          url: '/department/employee/delete',
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

$(".editemp").on("click", function(e){
  e.preventDefault();
  let id = $(this).attr('eid');
  $.ajax({
      url: `/department/employee/edit/${id}`,
      method: 'get',
      beforeSend:function(){
      },
      success:function(data){
          $("#hiddentID").val(id);
          $('#FirstName').val(data.FirstName);
          $('#LastName').val(data.LastName);
          $('#MiddleName').val(data.MiddleName);
          $('#EmploymentStatus').val(data.EmploymentStatus);
          $('#CurrentItem').val(data.CurrentItem);
          $('#SalaryGrade').val(data.SalaryGrade);
          $("#modalDeptEmployee").modal('toggle');
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
})

$(document).on("click", "#btnManualEnrolmentProceed", function(e){

  e.preventDefault();
  $.ajax({
    url: '/enrol/student-manual-enrol',
    method: 'post',
    data: $("#frmModalManualStudentEnroll").serialize(),
    cache: false,
    beforeSend:function(){
        $("#btnManualEnrolmentProceed").prop("disabled", true);
        $("#btnManualEnrolmentProceed").html("<i class = 'spinner-grow spinner-grow-sm'></i> Processing...");
        $("#manualmessage").html("<i class = 'spinner-grow spinner-grow-sm'></i> Saving...");
    },
    success:function(data){
        if (data.Error == 0){
          window.location.href = "view-enrolment?id="+data.Message;
          $("#btnManualEnrolmentProceed").prop("disabled", true);
          $("#btnManualEnrolmentProceed").html("<i class = 'spinner-grow spinner-grow-sm'></i> Redirecting...");
        }else{
          $("#btnManualEnrolmentProceed").prop("disabled", false);
          $("#btnManualEnrolmentProceed").html("Proceed");
          $("#manualmessage").html(data.Message);
        }

    }

  });
})

$(document).on("keyup", '#filterStudentNo', function(e){
  if (e.key === "Enter") {
    let id = $('#filterStudentNo').val();
    $.ajax({
      url: '/department/enrolment',
      method: 'post',
      data: {id},
      cache: false,
      beforeSend:function(){
          $("#ressearchstep2").html("<i class = 'spinner-grow spinner-grow-sm'></i> Generating...");
      },
      success:function(data){
          $("#ressearchstep2").html(data);

      }
    });
  }
});

$(document).on("click", "#btnTrack", function(e){
  e.preventDefault();
  $.ajax({
      url: '/department/track',
      method: 'post',
      data: $("#frmTracker").serialize(),
      cache: false,
      beforeSend:function(){
          $("#trackermsg").html("<i class = 'spinner-grow spinner-grow-sm'></i> Tracking...");
      },
      success:function(data){
          $("#trackermsg").html(data);
      },
      error: function (response) {

        if (response.status == 419){
            window.location.reload();
        }else{
            var errors = response.responseJSON.errors;
            $("#trackermsg").html('<div class="alert alert-danger">'+errors+'</div>');
        }
      }
  });
})

$("#btnManualSearchStudent").on("click", function(e){
      e.preventDefault();
      $.ajax({
          url: '{{ route("search-enrollee") }}',
          method: "POST",
          data: $("#frmManualSearchStudent").serialize(),
          beforeSend:function(){
              $("#manualmsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Searching, please wait...</div>");
          },
          success: function (data) {
            $("#manualmsg").html("");
            $("#modalManualSearchStudent").modal('toggle');
            window.open(
              data.url,
            '_blank'
            );
          },

          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#manualmsg").html(errors);
          }
      });
  })

</script>
