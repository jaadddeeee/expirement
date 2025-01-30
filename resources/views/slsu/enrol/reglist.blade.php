
<div class="table-responsive mt-2 mb-2">
    <table class="table table-sm">
      <thead>
        <tr>
          <td class = "text-nowrap">StudentNo</td>
          <td class = "text-nowrap">Name</td>
          <td class = "text-nowrap">FHE Status</td>
          <td class = "text-nowrap">Year/Section</td>
          <td class = "text-nowrap">Course</td>
          <td class = "text-nowrap">Date Processed</td>
          <td class = "text-nowrap">Mode</td>
        </tr>
      </thead>
      <tbody>
        @foreach($step1s as $step1)
        <tr>

          <td class = "text-nowrap"><a target = "_blank" href = "{{route('validate',['id' => Crypt::encryptstring($step1->StudentNo)])}}">{{$step1->StudentNo}}</a></td>
          <td class = "text-nowrap">{{$step1->FirstName . ' ' .$step1->LastName}}</td>
          <td class = "text-nowrap">{!!($step1->TES==1?'<span class = "text-success">FHE</span>':'<span class = "text-danger">NON-FHE</span>')!!}</td>
          <td class = "text-nowrap">{{(empty($step1->StudentYear)?"":$step1->StudentYear . ' / ' .$step1->Section)}}</td>
          <td class = "text-nowrap">{{$step1->accro}} ({{$step1->cur_num}}) {{(empty($step1->course_major)?"":" - ".$step1->course_major)}}</td>
          <td class = "text-nowrap">{{date('F j, Y', strtotime($step1->DateEnrolled))}}</td>
          <td class = "text-nowrap">{{$step1->WhereEnrolled}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
</div>

