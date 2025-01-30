{{$step1s->links()}}
<table class="table table-sm mt-2">
  <thead>
    <tr>
      <td class = "text-nowrap">#</td>
      <td class = "text-nowrap">StudentNo</td>
      <td class = "text-nowrap text-center">Records</td>
      <td class = "text-nowrap">Name</td>
      <td class = "text-nowrap text-center"># of Subjects</td>
      <td class = "text-nowrap">Course</td>
      <td class = "text-nowrap">Date Submitted</td>
      <td class = "text-nowrap">Additional Instruction</td>
    </tr>
  </thead>
  <tbody>
    @foreach($step1s as $step1)
    <?php
        $count = $step1->subjects->count();
        $textcolor = '';
        $text = utf8_decode($step1->FirstName . ' ' .$step1->LastName);
        if ($count > 0){
          $text = '<div title = "need finalize to continue" class= "badge bg-danger">'.$step1->FirstName . ' ' .$step1->LastName.'</div>';
          $textcolor = 'text-danger';
        }
    ?>
    <tr>
        <td>{{isset($ctr)?++$ctr:$ctr=1}}</td>
      @if (!empty($step1->StudentStatus))
        <td class = "text-nowrap"><a href = "{{route('pro-enrolment',['id' => Crypt::encryptstring($step1->StudentNo)])}}">{{$step1->StudentNo}}</a></td>
      @else
        <td class = "text-nowrap"><a class = "mdlsetStudentStatus" href = "#" data-bs-toggle="modal" data-bs-target="#mdlSetStatus" sname = "{{$step1->FirstName . ' ' .$step1->LastName}}" sid = "{{Crypt::encryptstring($step1->StudentNo)}}">{{$step1->StudentNo}}</a></td>
      @endif
      <td class = "text-nowrap text-center">
        <a target = "_blank" href = "{{route('view-one-student',['id' => Crypt::encryptstring($step1->StudentNo)])}}">
          <span class="text-primary mdi-24px mdi mdi-account-eye-outline"></span>
        </a>
      </td>
      <td class = "text-nowrap {{$textcolor}}">{!!$text!!}</td>
      <td class = "text-nowrap text-center">{{$count}}</td>
      <td class = "text-nowrap">{{$step1->accro}}{{(empty($step1->course_major)?"":" - ".$step1->course_major)}}</td>
      <td class = "text-nowrap">{{date('F j, Y', strtotime($step1->DateEncoded))}}</td>
      <td class = "">{!!wordwrap($step1->AdditionalIns, 30, '<br>')!!}</td>
    </tr>
    @endforeach
  </tbody>
</table>
{{$step1s->links()}}
