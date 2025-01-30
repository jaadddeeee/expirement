<script>

  $.ajaxSetup({
      headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  refresh();

  function refresh()
  {
    $.ajax({
        url: "/surveys",
        method: "POST",
        success: function (data) {
            $("#surveyBody").html(data);
        },
        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#surveyBody").html(errors);
        }
    });
  }

  $("#frmSurvey").on("submit", function(e){
      e.preventDefault();
      let id = $("#hiddenID").val();
      let url = id ? '/surveys/save/${id}' : '/surveys/save';
      let method = id ? 'PUT' : 'POST';

      $.ajax({
        url: url,
        method: method,
        data: $(this).serialize(),
        beforeSend:function(){
            $("#SurveyMsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
        },
        success: function (data) {
            $("#SurveyMsg").html('{!! GENERAL::Success("Survey title saved") !!}');
            $("#title").val('');
            $('#date_start').val('');
            $("#date_end").val('');
            $("#description").val('');
            $("#title").focus();
            refresh();
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#SurveyMsg").html(errors);
        }
      });
  })


  $("#frmSurveyQuestion").on("submit", function(e){
      e.preventDefault();
      let id = $("#hiddenQID").val();
      let url = id ? '/surveys/saveq/${id}' : '/surveys/saveq';
      let method = id ? 'PUT' : 'POST';

      $.ajax({
        url: url,
        method: method,
        data: $(this).serialize(),
        beforeSend:function(){
            $("#SurveyQuestionMsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
        },
        success: function (data) {
            window.location.reload();
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#SurveyQuestionMsg").html(errors);
        }
      });
  })

  $("#type").on("change", function(e){
     $('.option').hide();

     if ($(this).val() == "radio" || $(this).val() == "checkbox"){
      $('.option').show();
     }
  })
</script>
