<script>
  $.ajaxSetup({
      headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $(document).on("change", "#YearOfGraduation", function(e){
      let id = $("#YearOfGraduation").val();
      $.ajax({
        url: '/ched/report/graduation-list-dates',
        method: "POST",
        data: {id},
        beforeSend:function(){
            $("#DateOfGraduation").empty();
        },
        success: function (data) {
          $("#DateOfGraduation").append('<option value="0">Date of Graduation</option>');
          $.each(data, function (i, item) {
            $("#DateOfGraduation").append("<option value='"+item.grad+"'>" + item.grad  + " (" + item.cGrad + ")</option>");
          });
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            console.log(errors);
        }
      });
  })

  $(document).on("click", "#btnViewGraduateList", function(e){

    $.ajax({
        url: '/ched/report/graduation-list',
        method: "POST",
        data: $("#frmGraduationList").serialize(),
        beforeSend:function(){
            $("#bodyGraduation").html("");
            $("#btnViewGraduateList").prop("disabled", true).html("<i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...");
        },
        success: function (data) {
            $("#btnViewGraduateList").prop("disabled", false).html("VIEW");
            $("#bodyGraduation").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#bodyGraduation").html(errors);
            $("#btnViewGraduateList").prop("disabled", false).html("VIEW");
        }
      });
  })

  // $(document).on("click", "#btnPrintGraduateList", function(e){
  //   e.preventDefault();
  //   $.ajax({
  //       url: '/ched/report/generate-graduation-list',
  //       method: "POST",
  //       data: $("#frmGraduationList").serialize(),
  //       beforeSend:function(){

  //           $("#btnPrintGraduateList").prop("disabled", true).html("<i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...");
  //       },
  //       success: function (data) {
  //           $("#btnPrintGraduateList").prop("disabled", false).html('<i class = "fa fa-print"></i> Print');
  //           // var blob = new Blob([data]);
  //           // var link = document.createElement('a');
  //           // link.href = window.URL.createObjectURL(blob);
  //           // link.download = "storage/prcgraduation/{{session('campus')}}/{{session('fname')}}";
  //           // link.click();
  //           // document.location.href = "/storage/prcgraduation/{{session('campus')}}/{{session('fname')}}";
  //           window.open(
  //             data.filename,
  //             '_blank' // <- This is what makes it open in a new window.
  //           );
  //       },

  //       error: function (response) {
  //           var errors = response.responseJSON.errors;
  //           $("#bodyGraduation").html(errors);
  //           $("#btnPrintGraduateList").prop("disabled", false).html('<i class = "fa fa-print"></i> Print');
  //       }
  //   });
  // })
</script>
