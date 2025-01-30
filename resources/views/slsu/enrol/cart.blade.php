
<div class="table-responsive">
  <input hidden type = "text" value = "{{Crypt::encryptstring($one->StudentNo)}}" id = "hidStudentNo">
  <div class="row">
    <div class="col-sm-12 col-lg-6 col-md-6">
      <div class="d-flex align-items-start align-items-sm-center gap-4">
        <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
        <div class="button-wrapper">
            <div style = "font-size: 20px; font-weight: 600">{{strtoupper($one->FirstName. (empty($one->MiddleName)?' ':' '.$one->MiddleName[0].'. ') . $one->LastName)}}</div>
            <small>{{$reg->course->accro." (".$reg->cur_num.")"}}</small> {{(!empty($reg->major->course_major)?" | ".$reg->major->course_major:"")}}<br>
            <small><i class = "fa fa-send"></i> {!!(empty($one->ContactNo)?"No registered mobile":'<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">SMS '.$one->StudentNo.'</a>')!!} | {!!(empty($one->email)?"No e-mail registered":'<a href = "mailto:'.$one->email.'">'.$one->email.'</a>')!!}</small>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3 col-md-3">
      <div class="d-flex align-items-start align-items-sm-center gap-4">
        <div class="button-wrapper">
            <small>Program Level</small><br>
            <div style = "font-size: 15px; font-weight: 600">{{$reg->SchoolLevel}}</div>

            <small>Year/Section</small><br>
            <div style = "font-size: 15px; font-weight: 600">{{$reg->StudentYear."/".$reg->Section}}</div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3 col-md-3">
      <div class="d-flex align-items-start align-items-sm-center gap-4">
        <div class="button-wrapper">
            <small>Status</small><br>
            <div style = "font-size: 15px; font-weight: 600">{{$reg->StudentStatus}}</div>

            <small>Enrolled Units/Max Allowed Units</small><br>
            <div style = "font-size: 15px; font-weight: 600">{!!'<span class = "tmpUNits">'.$TotalEnrolled.'</span> / '.$MaxLimit!!}</div>
        </div>
      </div>
    </div>

  </div>
  <div class="divider">
    <div class="divider-text">Enrolled Subject (s)</div>
  </div>
  <div id = "CartMsg"></div>
  @if (empty($reg))
    <div class = 'alert alert-danger'>Not enrolled this sem</div>
  @else
    @if ($reg->subjects()->count() <= 0)
      <div class = 'alert alert-danger'>No enrolled subject</div>
    @else
        <table class = "table table-sm">
            <thead>
              <tr>
                <td class = "text-nowrap">Action</td>
                <td class = "text-nowrap">CourseCode</td>
                <td class = "text-nowrap">CourseNo</td>
                <td class = "text-nowrap">Description</td>
                <td class = "text-nowrap text-center">Units</td>
                <td class = "text-nowrap">Schedule</td>
              </tr>
            </thead>
            <tbody>
              <?php
                $totalUnits=0;
              ?>
        @foreach($subjects as $sub)
              <?php
                  $totalUnits+=($sub->exempt==1?0:$sub->units);
              ?>
              <tr id = "row-{{$sub->id}}">
                @if (!GENERAL::canEditEnrolment($reg->finalize))
                <td>&nbsp;</td>
                @else
                  @if($sub->RequireReqForm == 0)
                    <td class = "text-nowrap text-center" id = "td-{{$sub->id}}"><a class = "delSub" href = "#" sid = "{{$sub->id}}" snum = "{{Crypt::encryptstring($one->StudentNo)}}" gid = "{{Crypt::encryptstring($sub->id)}}"><i class = "text-danger fa fa-trash"></i></a></td>
                  @endif
                @endif

                <td class = "text-nowrap">{{$sub->coursecode}}</td>
                <td class = "text-nowrap">{{$sub->courseno}}</td>
                <td class = "text-nowrap">{{$sub->coursetitle}}</td>
                <td class = "text-nowrap text-center">{{($sub->exempt==1?"(".$sub->units.")":$sub->units)}}</td>
                <td class = "text-nowrap">{!!(empty($sub->Time1)?"":$sub->Time1).(empty($sub->Time2)?"":"<br>".$sub->Time2)!!}</td>
              </tr>
        @endforeach
              <tr>
                <td colspan = "4" class = "text-end">TOTAL UNITS:</td>
                <td class = "text-nowrap text-center fw-bolder">{{$totalUnits}}</td>
                <td class = "text-nowrap">&nbsp;</td>
              </tr>
            </tbody>
        </table>
    @endif
  @endif
</div>
