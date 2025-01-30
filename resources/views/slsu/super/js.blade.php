<script>
  $.ajaxSetup({
      headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $(document).on("click", ".tabGrades", function(e){
    e.preventDefault();
    let id = $("#hiddenStudentNo").val();
    let campus = $("#hiddenCampus").val();
    $.ajax({
        url: '/search/view-grades',
        method: "POST",
        data: {campus,id},
        beforeSend:function(){
            $("#tabGrades").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $("#tabGrades").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#tabGrades").html(errors);
        }
      });
  })

  $("#btnAssignProgram").on("click", function(e){
    e.preventDefault();
    $.ajax({
        url: '/super-admin/assigned-program',
        method: "POST",
        data: $("#frmAssignedProgram").serialize(),
        beforeSend:function(){
            $("#assignedprogrammsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Assigning, please wait...</div>");
        },
        success: function (data) {
          $("#btnViewClassess").click();
          setTimeout( function(){
            $("#offcanvasAssignedPrograms").offcanvas('show');
          }  , 500 );

        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#assignedprogrammsg").html(errors);
        }
    });
  })

  $("#btnImportHRMIS").on("click", function(e){
    e.preventDefault();
    $.ajax({
        url: '/super-admin/pro-import-hrmis',
        method: "POST",
        data: $("#frmImportHRMIS").serialize(),
        beforeSend:function(){
            $("#importresult").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Importing, please wait...</div>");
        },
        success: function (data) {
          $("#importresult").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#importresult").html(errors);
        }
    });
  })
</script>
