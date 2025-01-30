<script>
$.ajaxSetup({
    headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
$(document).on("change","#Campus", function(e){
      let id = $("#Campus").val();
      e.preventDefault();
      $.ajax({
          url: "/all/department-campus",
          method: 'post',
          cache: false,
          data: {id},
          beforeSend:function(){
            $("#Department").empty()
              .append('<option value="0">Generating...</option>');
          },
          success:function(data){
            $("#Department").empty().append('<option value="0">Select Department</option>');;
            $.each(data, function(i, item) {
              $("#Department").append('<option value="'+data[i].id+'">'+data[i].DepartmentName+'</option>');
            });
          },
          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#Department").empty()
              .append('<option value="0">'+errors+'</option>');
          }
      });
    })
</script>
