<table class = "mt-3 table table-sm">
    <thead>
      <tr>
        <td class = "text-nowrap">#</td>
        <td class = "text-nowrap">Employee Name</td>
        <td class = "text-nowrap">Department</td>
        <td class = "text-nowrap">Schedule</td>
        <td class = "text-nowrap"># of unencoded</td>
      </tr>
    </thead>
    <tbody>
        @php
        $ctr=1;
        @endphp
        @foreach($regs as $reg)
          <tr>
            <td>{{$ctr}}</td>
            <td>{{$reg->FirstName. ' '.$reg->LastName}}</td>
            <td>{{$reg->DepartmentName}}</td>
            <td>{{$reg->coursecode}}</td>
            <td>{{$reg->Unecoded}}</td>

          </tr>
          @php
            $ctr++;
          @endphp
        @endforeach
    <tbody>
</table>
