<script>
  $.ajaxSetup({
    headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});



$(document).on("click", "#btnSearchOfficialEnrolled", function(e){
    e.preventDefault();
    $.ajax({
      url: '/nstp/officially-enrolled',
      method: 'post',
      data: $("#frmNSTPMasterlist").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btnSearchOfficialEnrolled").prop("disabled", true);
          $("#btnSearchOfficialEnrolled").html("<i class = 'spinner-grow spinner-grow-sm'></i> Searching...");
          $("#outAjax").html("");
      },
      success:function(data){

          $("#btnSearchOfficialEnrolled").prop("disabled", false);
          $("#btnSearchOfficialEnrolled").html("View");
          $("#outAjax").html(data);

      },
      error: function (response) {
          $("#btnSearchOfficialEnrolled").prop("disabled", false);
          $("#btnSearchOfficialEnrolled").html("View");
          var errors = response.responseJSON.errors;
          $("#outAjax").html(errors);
      }

    });
});

$(document).on("click", "#btnSearchNoSerial", function(e){
    e.preventDefault();
    $.ajax({
      url: '/nstp/search-no-serial',
      method: 'post',
      data: $("#frmNSTPNoSerial").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btnSearchNoSerial").prop("disabled", true);
          $("#btnSearchNoSerial").html("<i class = 'spinner-grow spinner-grow-sm'></i> Searching...");
          $("#outAjax").html("");
      },
      success:function(data){

          $("#btnSearchNoSerial").prop("disabled", false);
          $("#btnSearchNoSerial").html("View");
          $("#outAjax").html(data);

      }

    });
});

$(document).on("keyup", ".studentname", function(e){
  if (e.key === 'Enter') {

    id = $(".studentname").val();
    $.ajax({
      url: "/nstp/studentsearch",
      method: 'post',
      cache: false,
      data: {id},
      beforeSend:function(){
        $("#ajaxcall").html("<div class = 'alert alert-warning'><i class = 'small spinner-grow small'></i> Searching, please wait...</div>");
      },
      success:function(data){

        $("#ajaxcall").html(data);

      }
    });
  }
});

</script>
