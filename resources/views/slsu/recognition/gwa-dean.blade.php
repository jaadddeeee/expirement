<div class = "table-responsive">
<div class = "mt-2 alert alert-info">Please note that this computation is tentative and subject to validation.</div>
<table class = "table table-sm mt-3">
    <thead>
      <tr>
        <td class = "text-nowrap">StudentNo</td>
        <td class = "text-nowrap">Name</td>
        <td class = "text-nowrap">Year Level</td>
        <td class = "text-nowrap">Major</td>
        <td class = "text-nowrap text-center">Units Earned</td>
        <td class = "text-nowrap text-center">GWA</td>
      </tr>
    </thead>
    <tbody>
      @foreach($all as $a)
        <tr>
          <td class = "text-nowrap">{{$a['StudentNo']}}</td>
          <td class = "text-nowrap">{{utf8_decode($a['LastName'].', '.$a['FirstName'])}}</td>
          <td class = "text-nowrap">{{$a['YearLevel']}}</td>
          <td class = "text-nowrap">{{$a['Major']}}</td>
          <td class = "text-nowrap text-center">{{$a['EarnedUnits']}}</td>
          <td class = "text-nowrap text-center">{{number_format($a['GWA'],3, '.','')}}</td>
        </tr>
      @endforeach
    </tbody>

</table>
</div>
