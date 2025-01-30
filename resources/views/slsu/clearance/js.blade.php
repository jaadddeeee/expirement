<script>
  $.ajaxSetup({
      headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $("#btnsavenewclearanceuser").on("click", function(e){
    e.preventDefault();
    $.ajax({
      url: '/clearance/student-save',
      method: 'post',
      data: $("#frmNewClearance").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btnsavenewclearanceuser").prop("disabled", true);
          $("#btnsavenewclearanceuser").html("<i class = 'spinner-grow spinner-grow-sm'></i> Saving...");
          $("#clearanceres").html("");
      },
      success:function(data){

          $("#btnsavenewclearanceuser").prop("disabled", false);
          $("#btnsavenewclearanceuser").html("<i class='bx bxs-user-plus'></i> Save");
          $("#clearanceres").html(data);
          $("#allsearch").val('');
          $("#Accountype").val('');
          $("#allsearch").focus();

      },
      error: function (response) {
          $("#btnsavenewclearanceuser").prop("disabled", false);
          $("#btnsavenewclearanceuser").html("<i class='bx bxs-user-plus'></i> Save");
          var errors = response.responseJSON.errors;
          $("#clearanceres").html(errors);
      }

    });
  })

  $(document).on("click",'.deleteclearance', function(e){
    e.preventDefault();
    let id = $(this).attr('cid');
    let sid = $(this).attr('sid');
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '/clearance/student-delete',
            method: 'post',
            data: {id},
            cache: false,
            beforeSend:function(){
              Swal.fire({
                  text: "Deleting...",
                  icon: "warning",
                  showCancelButton: false,
                  showConfirmButton: false,
              });
            },
            success:function(data){
              $("#sid-"+sid).hide();
              Swal.fire({
                  text: "Deleted",
                  icon: "success",
                  showCancelButton: false,
                  showConfirmButton: true,
              });

            },
            error: function (response) {

                var errors = response.responseJSON.errors;
                Swal.fire({
                  text: errors,
                  icon: "Error",
                  showCancelButton: false,
                  showConfirmButton: true,
                });
            }

          });
        }
    });
  })
</script>
