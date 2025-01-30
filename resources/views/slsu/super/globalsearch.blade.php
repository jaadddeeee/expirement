
<label>Found {{count($out)}} search result(s) for <b class = 'text-primary'>"{{$str}}"</b>.</label>
<div class="table-responsive mt-2">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
              <td class = "text-nowrap">#</td>
              <td class = "text-nowrap">Student Number</td>
              <td class = "text-nowrap">Last Name</td>
              <td class = "text-nowrap">First Name</td>
              <td class = "text-nowrap">Middle Name</td>
              <td class = "text-nowrap">Course</td>
              <td class = "text-nowrap">Campus</td>
            </tr>
        </thead>
        <tbody>
            @foreach($out as $one)
              <tr >
                <td class = "text-nowrap">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                @if (auth()->user()->AllowSuper == 1 or ROLE::isRegistrar())
                <td class = "text-nowrap"><a href = "{{route('admin-one-student',['snum' => Crypt::encryptstring($one['StudentNo']), 'campus' => Crypt::encryptstring($one['CampusIndex'])])}}">{{$one['StudentNo']}}</a></td>
                @elseif (ROLE::isDepartment())
                <td class = "text-nowrap"><a href = "{{route('view-one-student',['id' => Crypt::encryptstring($one['StudentNo']), 'campus' => Crypt::encryptstring($one['CampusIndex'])])}}">{{$one['StudentNo']}}</a></td>
                @endif
                <td class = "text-nowrap">{{utf8_decode($one['LastName'])}}</td>
                <td class = "text-nowrap">{{utf8_decode($one['FirstName'])}}</td>
                <td class = "text-nowrap">{{utf8_decode($one['MiddleName'])}}</td>
                <td class = "text-nowrap">{{$one['Course']}} ({{$one['CurNum']}})</td>
                <td class = "text-nowrap text-{{GENERAL::Campuses()[$one['CampusIndex']]['Color']}}"><i class = "fa {{GENERAL::Campuses()[$one['CampusIndex']]['Icon']}}"></i> {!! $one['Campus']!!}</td>
              </tr>
            @endforeach
        </tbody>
    </table>
</div>
