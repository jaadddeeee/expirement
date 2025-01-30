<script>
  $(".chkAll").on("click", function(){
    let val = $(this).val();
    $(".chk"+val).prop("checked",false);
    if ($(this).is(':checked')){
      $(".chk"+val).prop("checked","checked");
    }
  })
</script>
