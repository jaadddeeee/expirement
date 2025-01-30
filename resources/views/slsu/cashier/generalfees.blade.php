<div class = "row">
<div class="table-responsive mt-3 col-sm-12 col-lg-6">
    <table id="example" class="table table-sm table-hover">
        <thead>
            <tr >
                <th>&nbsp;</th>
                <th class = 'text-nowrap'>Fee Name</th>
                <th class = 'text-nowrap text-end'>Amount</th>

            </tr>
        </thead>
        <tbody>
        <?php
            $allBalance = 0;
        ?>
            @foreach($fees as $fee)


            <tr>
                <td><a href = "#" class = "delFee" rid = "{{$ID}}" sid = "{{Crypt::encryptstring($fee->id)}}"><i class = 'fa fa-trash-o text-danger'></i></a></th>
                <td class = 'text-nowrap'>{{$fee->item}}</td>
                <td class = 'text-nowrap text-end'><strong>{{number_format($fee->amount,2,'.',',')}}</strong></td>
            </tr>
            <?php
                $allBalance += (empty($fee->amount)?0:str_replace(",","",$fee->amount));
            ?>
           @endforeach
            <tr >
                <td>&nbsp;</th>
                <td class = 'text-nowrap text-end mr-2 h4'>TOTAL: </td>
                <td class = 'text-nowrap text-end h4'>{{number_format($allBalance,2,'.',',')}}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="table-responsive mt-3 col-sm-12 col-lg-6">

      <div class="mt-3 d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">ADD NEW FEE</h4>
        </div>
      </div>
      <hr>
      <div class="card-body">
          <form id = "frmAddFee">
            @csrf
            <div class = "form-group">
              <input type = "hidden" value = "{{$ID}}" name = "hiddenID">
              <input type = "hidden" value = "{{$Table}}" name = "hiddenTable">
              <label>Fee Name / Code</label>
              <input type="text" id="allfees" name = "allfees" class="mb-2 typeahead form-control" placeholder="Fee Name / Code">
            </div>

            <div class = "form-group">
              <label>Amount</label>
              <input type="number" id="amount" name = "amount" class="mb-2 form-control" placeholder="Amount">
            </div>
          </form>
          <button id = "AddFee" class = "btn btn-primary">Add Fee</button>
          <div id = "errorfee"></div>
      </div>


</div>

</div>

<script>

var pathfee = "/typeahead/fees";

$('#allfees').typeahead({
  source: function (str, process) {
      return $.get(pathfee, {
          str: str
      }, function (data) {
          return process(data);
      });
  }

});

</script>
