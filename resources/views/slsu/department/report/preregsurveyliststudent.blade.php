
<div class = "table-responsive">
  <table class = "table table-sm table-hover">
    <thead>
      <tr>
        <td class = "text-nowrap">StudentNo</td>
        <td class = "text-nowrap">Student</td>
        <td class = "text-nowrap">Major</td>
        <td class = "text-nowrap text-center">Send SMS</td>
        <td class = "text-nowrap text-center">Year / Section</td>
        <td class = "text-nowrap">Status</td>
        <td class = "text-nowrap">Pre-reg Date</td>
      </tr>
    </thead>
    <tbody>
      @foreach($students as $student)
        <?php
            $status = "<span class = 'text-danger'>Not Encoded</span>";
            foreach($regs as $reg){
                if ($reg->StudentNo == $student->StudentNo){
                    if ($reg->finalize == 1)
                      $status = "<span class = 'text-dark'>Validated</span>";
                    else
                      $status = "<span class = 'text-success'>On-Going</span>";
                }
            }
        ?>
        <tr>
          <td class = "text-nowrap">{{$student->StudentNo}}</td>
          <td class = "text-nowrap">{{$student->LastName.', '.$student->FirstName}}</td>
          <td class = "text-nowrap">{{$student->course_major}}</td>
          <td class = "text-nowrap text-center"><a class = "sendsmsprereg" snumber = "{{Crypt::encryptstring($student->StudentNo)}}" sname = "{{$student->LastName.', '.$student->FirstName}}" href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop"><i class='bx bx-chat'></i></a></td>
          <td class = "text-nowrap text-center">{{$student->StudentYear." / ".$student->Section}}</td>
          <td class = "text-nowrap">{!!$status!!}</td>
          <td class = "text-nowrap">{{date('Y-M-d h:i:s A', strtotime($student->created_at))}}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

