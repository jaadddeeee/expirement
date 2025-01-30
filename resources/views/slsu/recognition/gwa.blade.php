<div class = "table-responsive">

<table class = "table table-sm mt-3">
    <thead>
      <tr>
        <td class = "text-nowrap">StudentNo</td>
        <td class = "text-nowrap">Name</td>
        <td class = "text-nowrap">Major</td>
        <td class = "text-nowrap text-center">Units Earned</td>
        <td class = "text-nowrap text-center">GWA</td>
        <td class = "text-nowrap text-center">GWA (If OJT is 1.0)</td>
      </tr>
    </thead>
    <tbody>
      @foreach($all as $a)
        <tr>
          <td class = "text-nowrap">{{$a['StudentNo']}}</td>
          <td class = "text-nowrap">{{utf8_decode($a['LastName'].', '.$a['FirstName'])}}</td>
          <td class = "text-nowrap">{{$a['Major']}}</td>
          <td class = "text-nowrap text-center">{{$a['EarnedUnits']}}</td>
          <td class = "text-nowrap text-center">{{number_format($a['GWA'],3, '.','')}}</td>
          <td class = "text-nowrap text-center">{{number_format($a['GWA2'],3, '.','')}}</td>
        </tr>
      @endforeach
    </tbody>

</table>
</div>
