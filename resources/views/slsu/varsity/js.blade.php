<script>
    $.ajaxSetup({
        headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
  
    $(document).on("click", "#btn-save", function(e){
    e.preventDefault();
    $.ajax({
      url: '/varsity/save-event',
      method: 'post',
      data: $("#frmAdd").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btn-save").prop("disabled", true);
          $("#btn-save").html("<i class = 'spinner-grow spinner-grow-sm'></i> Adding...");
          $("#msg").html("");
      },
      success:function(data){

          $("#btn-save").prop("disabled", false);
          $("#btn-save").html("Add to list");

          if (data.Error == 1){
              $("#msg").html(data.Message).addClass("rounded alert-danger");
          }

          if (data.Error == 0){
            $("#msg").html(data.Message).addClass("rounded alert-success");
            setTimeout(function() {
              $("#event").val("");
              $('input[name="event"]').focus();
            }, 1000);

        }
          // $("#outAjax").html(data);

      }

    });
});

$(".editEvent").on("click", function(e){
  e.preventDefault();
  let id = $(this).attr('data-id');

  window.history.pushState({}, "", `/varsity/edit-event/${id}`);
  $.ajax({
      url: `/varsity/edit-event/${id}`,
      method: 'get',
      beforeSend:function(){
      },
      success:function(data){
          $("#hiddentID").val(id);
          $('#updateEvent').val(data.event);
          $("#updateModalEvent").modal('toggle');
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
});

$(document).on("click", "#btn-update", function(e){
    e.preventDefault();
    // let id = $("#updateHiddentID").val();
    $.ajax({
      url: "{{ route('update-event') }}",
      method: 'patch',
      data: $("#frmUpdate").serialize(),
      cache: false,
      beforeSend:function(){
          $("#btn-update").prop("disabled", true);
          $("#btn-update").html("<i class = 'spinner-grow spinner-grow-sm'></i> Updating...");
          $("#msg").html("");
      },
      success:function(data){

          $("#btn-update").prop("disabled", false);
          $("#btn-update").html("Update the list");

          if (data.Error == 1){
              $("#updatemsg").html(data.Message);
          }

          if (data.Error == 0){
            $("#updatemsg").html(data.Message);
            setTimeout(function() {
              $("#updateEvent").val("");
              $('input[name="updateEvent"]').focus();
            }, 1000);

        }
          // $("#outAjax").html(data);

      }

    });
});

$(document).on("hidden.bs.modal", "#modalEvent", function(){
    window.location.reload();
});

$(document).on("hidden.bs.modal", "#updateModalEvent" , function(){
    window.history.pushState({}, "", "/varsity/event");
    window.location.reload();
});
</script>
  