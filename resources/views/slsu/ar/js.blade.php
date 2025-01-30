<script>

  $.ajaxSetup({
      headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });




  $("#btnGeneratePaidTuition").on("click", function(e){
      e.preventDefault();
      let campus = $("#Campus").val();
      let datefrom = $("#datefrom").val();
      let dateto = $("#dateto").val();
      $.ajax({
        url: '/accounts-receivable/generate-tuition',
        method: "POST",
        data: {datefrom,campus,dateto},
        beforeSend:function(){
            $("#outAjax").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
            $("#btnGeneratePaidTuition").prop("disabled", true);
        },
        success: function (data) {
            $("#btnGeneratePaidTuition").prop("disabled", false);
            $("#outAjax").html(data);
        },

        error: function (response) {
            $("#btnGeneratePaidTuition").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#outAjax").html(errors);
        }
      });
  })

  $("#btnGenerateTable").on("click", function(e){
      e.preventDefault();
      let campus = $("#Campus").val();
      let datefrom = $("#datefrom").val();
      let dateto = $("#dateto").val();
      $.ajax({
        url: '/accounts-receivable/generate',
        method: "POST",
        data: {datefrom,campus,dateto},
        beforeSend:function(){
            $("#outAjax").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
            $("#btnGenerateTable").prop("disabled", true);
        },
        success: function (data) {
            $("#btnGenerateTable").prop("disabled", false);
            $("#outAjax").html(data);
        },

        error: function (response) {
            $("#btnGenerateTable").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#outAjax").html(errors);
        }
      });
  })

</script>
