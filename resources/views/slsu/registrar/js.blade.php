<script>
  $.ajaxSetup({
      headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $(document).on("click", ".reglistsearch", function(e){
    e.preventDefault();
    $.ajax({
        url: '{{ route("step4list-pro") }}',
        method: "POST",
        data: $("#frmRegSearchList").serialize(),
        beforeSend:function(){
            $("#regselectresult").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $(".dropdown-list").hide();
            $("#regselectresult").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#regselectresult").html(errors);
        }
    });
  })

  $("#modalManualSearchStudent").on("shown.bs.modal", function(e){
    $("#str").focus();
    $("#str").val('');
    $("#SchoolYear").val(0);
    $("#Semester").val(0);
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
