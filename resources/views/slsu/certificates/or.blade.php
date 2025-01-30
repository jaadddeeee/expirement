<script>
  $.ajaxSetup({
    headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$("#addon-orno1, #addon-orno2").click(function(){
    $("#lblORNo").text("ORNo: ");
    $("#lblPayor").text("Payor: ");
    $("#lblDatePaid").text("Date Paid: ");
    $("#lblAmount").text("Amount: ");
    $("#searchorno").val("");
    $("#searchorres").html("");
    $("#hidORNo").val("");
    $("#hidDatePaid").val("");
})

$("#addon-orno2").click(function(){
    $("#hidButtonClick").val(2);
});

$("#addon-orno1").click(function(){
    $("#hidButtonClick").val(1);
});

$(document).on("click", "#btnsearchor", function(e){
  e.preventDefault();
  // alert("FF");
  $.ajax({
    url: '/search/or',
    method: 'post',
    data: $("#frmSearchOR").serialize(),
    beforeSend:function(){
      $("#lblORNo").text("ORNo: ");
      $("#lblPayor").text("Payor: ");
      $("#lblDatePaid").text("Date Paid: ");
      $("#lblAmount").text("Amount: ");
      $("#searchorres").html("");
      $("#hidORNo").val("");
      $("#hidDatePaid").val("");
    },
    success:function(data){
      $("#lblORNo").text("ORNo: "+data.ORNo);
      $("#lblPayor").text("Payor: "+data.Payor);
      $("#lblDatePaid").text("Date Paid: "+data.date_paid);
      $("#lblAmount").text("Amount: "+data.Amount);
      $("#hidORNo").val(data.ORNo);
      $("#hidDatePaid").val(data.date_paid);

    },
    error: function (response) {
      $("#lblORNo").text("ORNo: ");
      $("#lblPayor").text("Payor: ");
      $("#lblDatePaid").text("Date Paid: ");
      $("#lblAmount").text("Amount: ");
      $("#hidORNo").val("");
      $("#hidDatePaid").val("");
      if (response.status == 419){
          window.location.reload();
      }else{
          var errors = response.responseJSON.errors;
          $("#searchorres").html(errors);
      }
    }
  });
})

$(".editemp").on("click", function(e){
  e.preventDefault();
  let id = $(this).attr('eid');
  $.ajax({
      url: `/department/employee/edit/${id}`,
      method: 'get',
      beforeSend:function(){
      },
      success:function(data){
          $("#hiddentID").val(id);
          $('#FirstName').val(data.FirstName);
          $('#LastName').val(data.LastName);
          $('#MiddleName').val(data.MiddleName);
          $('#EmploymentStatus').val(data.EmploymentStatus);
          $('#CurrentItem').val(data.CurrentItem);
          $('#SalaryGrade').val(data.SalaryGrade);
          $("#modalDeptEmployee").modal('toggle');
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
})

$("#ORUseValue").on("click", function(e){
  e.preventDefault();
  if ($("#hidButtonClick").val() == 1){
    $("#ORNo").val($("#hidORNo").val());
    $("#ORDate").val($("#hidDatePaid").val());
  }else{
    $("#DocORNo").val($("#hidORNo").val());
    $("#DocORDate").val($("#hidDatePaid").val());
  }
  $("#btnORNOCancel").click();
})
</script>
