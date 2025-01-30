<?php
      $pref = GENERAL::getStudentDefaultEnrolment();
      $sy = "";
      $sem = "";
      $ok = false;
      if (!empty($pref)){
        $sy = $pref['SchoolYear'];
        $sem = $pref['Semester'];
        $ok = true;
      }
      $match = false;
?>
<div class="header-title mt-4">
  <h4 class="card-title">{{ utf8_decode(strtoupper($tmpStudent->FirstName . (empty($tmpStudent->MiddleName)?' ':' '.$tmpStudent->MiddleName[0].'. ') .$tmpStudent->LastName)) }} {!!($tmpStudent->Sex=="M"?'<i class = "fa fa-male text-primary"></i>':'<i class = "fa fa-female text-danger"></i>')!!}</h4>
  <label>{{$tmpStudent->StudentNo}} / {{$tmpStudent->accro." (".$tmpStudent->cur_num.")"}}{{(empty($tmpStudent->course_major)?"":" / ".$tmpStudent->course_major)}}</label><br>
  <label><i class = "fa fa-mobile"></i> {!!(empty($tmpStudent->ContactNo)?"No registered mobile":'<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">'.$tmpStudent->ContactNo.'</a>')!!} <i class = "fa fa-send-o"></i> {!!(empty($tmpStudent->email)?"No e-mail registered":'<a href = "mailto:'.$tmpStudent->email.'">'.$tmpStudent->email.'</a>')!!}</label>
</div>
<hr>
<div class="table-responsive">
    @if (empty($registrations))
        <div class="alert alert-info">This student has no history of enrolment.</div>
    @else
        <table class="table table-sm">
            <thead>
                <tr>
                  <td class = "text-nowrap">#</td>
                  <td class = "text-nowrap">School Year</td>
                  <td class = "text-nowrap">Semester</td>
                  <td class = "text-nowrap">Status</td>
                  <td class = "text-nowrap">Student Year</td>
                  <td class = "text-nowrap">Course</td>
                  <td class = "text-nowrap">Scholarship</td>
                  <td class = "text-nowrap">isFHE?</td>
                  <td class = "text-nowrap">Action</td>
                </tr>
            </thead>
            <tbody>

              @foreach($registrations as $reg)
                <?php
                    $action = "";
                ?>
                @if ($reg->Semester == $sem and $reg->SchoolYear == $sy)
                  <?php
                    $match = true;
                    $action = "<a class = 'fherevoke' sid = '".Crypt::encryptstring($tmpStudent->StudentNo)."' href = '#'><i class = 'text-danger fa fa-history'></i></a>";
                  ?>
                @endif
                <tr>
                  <td>{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                  <td class = "text-nowrap">{{GENERAL::setSchoolYearLabel($reg->SchoolYear, $reg->Semester)}}</td>
                  <td class = "text-nowrap">{{GENERAL::Semesters()[$reg->Semester]['Long']}}</td>
                  <td class = "text-nowrap">{{$reg->StudentStatus}}</td>
                  <td class = "text-nowrap">{{$reg->StudentYear}}</td>
                  <td class = "text-nowrap">{{$reg->accro}} {{empty($reg->course_major)?"":" - ".$reg->course_major}}</td>
                  <td class = "text-nowrap">{{$reg->scholar_name}}</td>
                  <td class = "text-nowrap">{{$reg->TES==1?"YES":"NO"}}</td>
                  <td class = "text-nowrap">{!!$reg->TES==0?"":$action!!}</td>
                </tr>
              @endforeach
            </tbody>
        </table>
    @endif

    @if (!$match)
      <div class="alert alert-danger mt-3">
          {{ utf8_decode(strtoupper($tmpStudent->FirstName . (empty($tmpStudent->MiddleName)?' ':' '.$tmpStudent->MiddleName[0].'. ') .$tmpStudent->LastName)) }} will be marked as <strong>Free Higher Education</strong> beneficiary this
          <strong>{{(empty($sy)?"ERROR":GENERAL::setSchoolYearLabel($sy, $sem))}} {{(empty($sem)?"ERROR":GENERAL::Semesters()[$sem]['Long'])}}</strong>. Please select from the options below.<br>
          <button sid = "{{Crypt::encryptstring($tmpStudent->StudentNo)}}" class = "btnMarkFHE mt-3 me-2 btn btn-success">Yes, mark as FHE</button>
          <button sid = "{{Crypt::encryptstring($tmpStudent->StudentNo)}}" class = "btnMarkNONFHE mt-3 btn btn-danger">No, not qualified for FHE</button>
      </div>
    @else
      <div class="alert alert-info mt-3">
          This student has been processed.
      </div>
    @endif
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneSMSStudent" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-send"></i> Send SMS to {{ utf8_decode(strtoupper($tmpStudent->FirstName . (empty($tmpStudent->MiddleName)?' ':' '.$tmpStudent->MiddleName[0].'. ') .$tmpStudent->LastName)) }}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmSendOneSMS">
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">
        <input type = "hidden" name = "hidStudentID" id = "hidStudentID" value = "{{Crypt::encryptstring($tmpStudent->StudentNo)}}">
        <div id = "onesms"></div>
        <label>Enter your message. (Limited to 155 per SMS)</label>
        <textarea name = "BulkMessage" class = "form-control mb-3" rows = "10" autofocus></textarea>
        <button type="button" id = "btnsendonesms" class="btn btn-primary mb-2  w-100">Send Now</button>
        <button type="button" class="btn btn-outline-secondary  w-100" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
</div>
