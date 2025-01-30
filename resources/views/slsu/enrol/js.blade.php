<script>
  $.ajaxSetup({
    headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$(document).on("click", "#btnSaveEnrol", function(e){
    e.preventDefault();
    $.ajax({
      url: '/enrol/save',
      method: 'post',
      data: $("#frmEnrol").serialize(),
      cache: false,
      dataType: 'json',
      beforeSend:function(){
        Swal.fire({
            position: 'center',
            icon: 'info',
            title: "Saving...",
            text: "Please wait...",
            showConfirmButton: false,
        })
      },
      success:function(data){
        console.log(data);
        if (data.Error == 0){

          Swal.close();

          $(".tmpUNits").html(data.Units);

          if (data.ErrorRaise != ""){
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: "Error!",
                html: data.ErrorRaise,
                showConfirmButton: true,
            })
          }

          $.each(data.OKRaise, function(v,k) {

            $("#chk-"+k.pri).prop("disabled", true)
                  .prop("checked", false);
            $("#sched-"+k.pri).html(k.Time);
            $("#final-"+k.pri).html(k.Grade );

          });


        }else{
          Swal.fire({
              position: 'center',
              icon: 'error',
              title: "Error!",
              html: data.Message,
              showConfirmButton: true,
          })
        }

      }

    });
});

$("#showCart").on("show.bs.modal", function(){

    let id = $("#idCart").attr("sid");
    $.ajax({
      url: '/enrol/cart',
      method: 'post',
      data: {id},
      cache: false,
      beforeSend:function(){
        $("#bodyCart").html('<div class="spinner-grow text-danger" role="status"><span class="visually-hidden">Loading...</span></div> Retrieving...')
      },
      success:function(data){

        $("#bodyCart").html(data);

      }

    });
});

$(document).on("click", ".delSub", function(e){
  e.preventDefault();
  let id = $(this).attr("gid");
  let sid = $(this).attr("sid");
  let snum = $(this).attr("snum");
  $("#showCart").modal("toggle");
  Swal.fire({
    title: "Do you want to delete the selected subject?",
    icon: 'question',
    showDenyButton: false,
    showCancelButton: true,
    confirmButtonText: "Delete",
  }).then((result) => {

    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {

      $.ajax({
        url: '/enrol/delete-cart',
        method: 'post',
        data: {id,snum},
        cache: false,
        dataType: 'json',
        beforeSend:function(){
          $("#CartMsg").html("");
          $("#td-"+sid).html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>')
        },
        success:function(data){
          console.log(data);
          if (data.Error == 0){
            $("#row-"+sid).hide();
            $(".tmpUNits").html(data.Message);
            $("#sched-"+data.pri).html(data.Schedule);
            $("#chk-"+data.pri).prop("disabled", false)
                  .prop("checked", false);
            $("#final-"+data.pri).html("");
            $("#showCart").modal("toggle");
          }else{
            $("#td-"+sid).html('<a class = "delSub" snum = "'+snum+'" href = "#" sid = "'+sid+'" gid = "'+id+'"><i class = "text-danger fa fa-trash"></i></a>')
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: "Error!",
                html: data.Message,
                showConfirmButton: true,
            }).then((result) => {
              if (result.isConfirmed){
                $("#showCart").modal("toggle");
              }
            });
          }
        }
      });
    }

    if (result.isDismissed){
      $("#showCart").modal("toggle");
    }

  });
})

$(document).on("click", "#btnFinalizeDepartment", function(e){
  e.preventDefault();
  let snum = $("#hidStudentNo").val();
  $.ajax({
    url: '/enrol/finalize',
    method: 'post',
    data: {snum},
    cache: false,
    dataType: 'json',
    beforeSend:function(){
      $("#btnFinalizeDepartment").prop("disabled", true);
      $("#CartMsg").html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>');
    },
    success:function(data){
        $("#btnFinalizeDepartment").prop("disabled", false);
        if (data.Error == 0){
          $("#btnFinalizeDepartment").hide();
          $(".delSub").hide();
          $(".enrolchk").prop("disabled", true)
            .prop("checked", false);
          $("#CartMsg").html('<div class="alert alert-success">Student has been marked as finalized. You can no longer add or edit the enrolled subject(s).</div>');
        }else{
          $("#CartMsg").html(data.Message);
        }
    }
  });
});

$(document).on("click", "#btnValidate", function(e){
  e.preventDefault();

  Swal.fire({
    title: "Confirm Validation?",
    text: "This will mark the student as validated. You can no longer revert this.",
    icon: 'question',
    showDenyButton: false,
    showCancelButton: true,
    confirmButtonText: "VALIDATE",
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {

      $.ajax({
        url: '/registrar/validate-pro',
        method: 'post',
        data: $("#frmValidate").serialize(),
        cache: false,
        beforeSend:function(){
          Swal.fire({
            title: "Validating!",
            text: "Please wait while the system is validating the student!",
            icon: "info",
            showConfirmButton: false,
          });
        },
        success:function(data){
          let timerInterval;
          Swal.fire({
            icon: "success",
            title: "Validated!",
            html: "Page will auto refresh in <b></b> milliseconds.",
            timer: 1000,
            timerProgressBar: true,
            didOpen: () => {
              Swal.showLoading();
              const timer = Swal.getPopup().querySelector("b");
              timerInterval = setInterval(() => {
                timer.textContent = `${Swal.getTimerLeft()}`;
              }, 100);
            },
            willClose: () => {
              clearInterval(timerInterval);
            }
          }).then((result) => {
            window.location.reload();
          });
        },
        error: function (response) {
            var errors = response.responseJSON.errors;
            Swal.fire({
              title: "Error!",
              text: errors,
              icon: "error",
              showConfirmButton: true,
            });
        }
      });
    }
  });
});

$(".withdrawenrolment").on("click", function(e){
  e.preventDefault();
  let regid = $(this).attr('regid');
  Swal.fire({
    title: "Are you sure?",
    text: "You will withdraw this student this semester. You won't able to revert this.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, withdraw it!"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/registrar/withdraw',
        method: 'post',
        data: {regid},
        cache: false,
        beforeSend:function(){
          Swal.fire({
            title: "Withdrawing!",
            text: "Please wait while the system is withdrawing the student!",
            icon: "info",
            showConfirmButton: false,
          });
        },
        success:function(data){
          let timerInterval;
          Swal.fire({
            icon: 'success',
            title: "Widthrawn",
            html: "Please wait while redirecting you in <b></b> milliseconds.",
            timer: 1000,
            timerProgressBar: true,
            didOpen: () => {
              Swal.showLoading();
              const timer = Swal.getPopup().querySelector("b");
              timerInterval = setInterval(() => {
                timer.textContent = `${Swal.getTimerLeft()}`;
              }, 100);
            },
            willClose: () => {
              clearInterval(timerInterval);
            }
          }).then((result) => {
              window.location.href = "/registrar/enrolment";
          });
        },
        error: function (response) {
            var errors = response.responseJSON.errors;
            Swal.fire({
              title: "Error!",
              text: errors,
              icon: "error",
              showConfirmButton: true,
            });
        }
      });
    }
  });
})

$(document).on("click", "#btnDropManualSubject", function(e){
  e.preventDefault();
  $.ajax({
    url: '/enrol/pro-drop-subject',
    method: 'post',
    data: $("#frmDropSubject").serialize(),
    cache: false,
    beforeSend:function(){
      $(".dropmsg").html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Dropping...</span></div>');
    },
    success:function(data){
      $(".dropmsg").html(data);
      setTimeout(function(){
         window.location.reload()
      }, 1000);
    },
    error: function (response) {
        var errors = response.responseJSON.errors;
        $(".dropmsg").html(errors);
    }
  });
})

$(document).on("change", "#AddSubjects",function(){
  let id = $(this).val();
  let sy = "{{$reg->SchoolYear}}";
  let sem = "{{$reg->Semester}}";
  $.ajax({
    url: '/enrol/addschedule-manual',
    method: 'post',
    data: {id,sy,sem},
    cache: false,
    beforeSend:function(){
      $("#addmsg").html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>');
    },
    success:function(data){
      $(".resAddSelect").html(data);
    },
    error: function (response) {
        var errors = response.responseJSON.errors;
        $("#addmsg").html(errors);
    }
  });
})

$(document).on("change", "#ModifySubjects",function(){
  let id = $(this).val();
  let sy = "{{$reg->SchoolYear}}";
  let sem = "{{$reg->Semester}}";
  $.ajax({
    url: '/enrol/addschedule-manual',
    method: 'post',
    data: {id,sy,sem},
    cache: false,
    beforeSend:function(){
      $("#modifymsg").html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>');
    },
    success:function(data){
      $(".resModifySelect").html(data);
    },
    error: function (response) {
        var errors = response.responseJSON.errors;
        $("#modifymsg").html(errors);
    }
  });
})

$(document).on("click", "#btnAddManualSubject", function(e){
  e.preventDefault();
  $.ajax({
    url: '/enrol/pro-add-subject',
    method: 'post',
    data: $("#frmAddSubject").serialize(),
    cache: false,
    beforeSend:function(){
      $(".addmsg").html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div>');
    },
    success:function(data){
      $(".addmsg").html(data);
      setTimeout(function(){
         window.location.reload()
      }, 1000);
    },
    error: function (response) {
        var errors = response.responseJSON.errors;
        $(".addmsg").html(errors);
    }
  });
})

$(document).on("click", "#btnModifyManualSubject", function(e){
  e.preventDefault();
  $.ajax({
    url: '/enrol/pro-modify-subject',
    method: 'post',
    data: $("#frmModifySubject").serialize(),
    cache: false,
    beforeSend:function(){
      $(".modifymsg").html('<div class="spinner-grow spinner-grow-sm text-danger" role="status"><span class="visually-hidden">Changing...</span></div>');
    },
    success:function(data){
      $(".modifymsg").html(data);
      setTimeout(function(){
         window.location.reload()
      }, 1000);
    },
    error: function (response) {
        var errors = response.responseJSON.errors;
        $(".modifymsg").html(errors);
    }
  });
})


</script>
