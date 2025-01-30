<div class="row">
  <div class="table-responsive">
    <table class="table table-sm">
      <thead>
        <tr>
          <th class = "text-nowrap">#</th>
          <th class = "text-nowrap">Payor</th>
          <th class = "text-nowrap">AccountID</th>
          <th class = "text-nowrap">Description</th>
          <th class = "text-nowrap text-end">Amount</th>
          <th class = "text-nowrap">Date Paid</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $total = 0;
          // dd($out);
        ?>
        @foreach($out as $o)
        <?php
          $total += $o['Amount'];
        ?>
        <tr>
          <td class = "text-nowrap">{{isset($ctr)?++$ctr:$ctr=1}}</td>
          <td class = "text-nowrap">{{$o['Payor']}}</td>
          <td class = "text-nowrap">{{$o['AccountID']}}</td>
          <td class = "text-nowrap">{{$o['Particular']}}</td>
          <td class = "text-nowrap text-end">{{number_format($o['Amount'],2)}}</td>
          <td class = "text-nowrap">{{date('F j, Y',strtotime($o['DatePaid']))}}</td>
        </tr>
        @endforeach
      </tbody>
        <tr>
          <td class = "text-nowrap"></td>
          <td class = "text-nowrap"></td>
          <td class = "text-nowrap"></td>
          <td class = "text-nowrap text-end">TOTAL</td>
          <td class = "text-nowrap text-end">{{number_format($total,2)}}</td>
          <td class = "text-nowrap"></td>
        </tr>
    </table>
  </div>
</div>
