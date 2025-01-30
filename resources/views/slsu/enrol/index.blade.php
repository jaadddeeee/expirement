@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
<style>
  .fab-container {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    user-select: none;
    position: fixed;
    bottom: 30px;
    right: 30px;
  }
  .fab-container:hover {
    height: 100%;
  }
  .fab-container:hover .sub-button:nth-child(2) {
    transform: translateY(-80px);
  }
  .fab-container:hover .sub-button:nth-child(3) {
    transform: translateY(-140px);
  }
  .fab-container:hover .sub-button:nth-child(4) {
    transform: translateY(-200px);
  }
  .fab-container:hover .sub-button:nth-child(5) {
    transform: translateY(-260px);
  }
  .fab-container:hover .sub-button:nth-child(6) {
    transform: translateY(-320px);
  }
  .fab-container .fab {
    position: relative;
    height: 70px;
    width: 70px;
    background-color: #4ba2ff;
    border-radius: 50%;
    z-index: 2;
  }
  .fab-container .fab::before {
    content: " ";
    position: absolute;
    bottom: 0;
    right: 0;
    height: 35px;
    width: 35px;
    background-color: inherit;
    border-radius: 0 0 10px 0;
    z-index: -1;
  }
  .fab-container .fab .fab-content {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
    border-radius: 50%;
  }
  .fab-container .fab .fab-content .material-icons {
    color: white;
    font-size: 48px;
  }
  .fab-container .sub-button {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    bottom: 10px;
    right: 10px;
    height: 50px;
    width: 50px;
    background-color: #4ba2ff;
    border-radius: 50%;
    transition: all 0.3s ease;
  }
  .fab-container .sub-button:hover {
    cursor: pointer;
  }
  .fab-container .sub-button .material-icons {
    color: white;
    padding-top: 6px;
  }
</style>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="{{route('step1lists')}}">Step 1 lists</a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">{{$pageTitle}}</a>
    </li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12">

    <div class="card mb-4">
      <h5 class="card-header">Student Details</h5>
      <!-- Account -->
      <div class="card-body">
        <div class="row">
          <div class="col-sm-12 col-lg-6 col-md-6">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
              <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
              <div class="button-wrapper">
                  <div style = "font-size: 20px; font-weight: 600">{{$one->StudentNo}} | {{strtoupper($one->FirstName. (empty($one->MiddleName)?' ':' '.$one->MiddleName[0].'. ') . $one->LastName)}}</div>
                  <small>{{$CourseString." (".$CurNum.")"}}</small>{!!(empty($MajorString)?"":"<small> | ".$MajorString."</small>")!!}<br>
                  <small><i class = "fa fa-send"></i> {!!(empty($one->ContactNo)?"No registered mobile":'<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">SMS '.$one->StudentNo.'</a>')!!} | {!!(empty($one->email)?"No e-mail registered":'<a href = "mailto:'.$one->email.'">'.$one->email.'</a>')!!}</small>
                  <div class = "mt-1">
                    <a class = "badge badge-sm rounded-pill bg-primary text-white" target = "_blank" href = "{{route('view-one-student',['id' => Crypt::encryptstring($one->StudentNo)])}}">
                      <small>View Records</small>
                    </a>

                  </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-3 col-md-3">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
              <div class="button-wrapper">
              <small>Status</small><br>
              <div style = "font-size: 15px; font-weight: 600">{{$reg->StudentStatus}}</div>

                  <small>Year/Section</small><br>
                  <div style = "font-size: 15px; font-weight: 600">{{$reg->StudentYear."/".$reg->Section}}</div>
                  <a class = "mdlsetStudentStatus text-danger" href = "#" data-bs-toggle="modal" data-bs-target="#mdlSetStatus" sname = "{{$one->FirstName . ' ' .$one->LastName}}" sid = "{{Crypt::encryptstring($reg->StudentNo)}}">change</a>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-lg-3 col-md-3">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
              <div class="button-wrapper">

                  <small>Program Level</small><br>
                  <div style = "font-size: 15px; font-weight: 600">{{$reg->SchoolLevel}}</div>
                  <small>Enrolled Units/Max Allowed Units</small><br>
                  <div style = "font-size: 15px; font-weight: 600">{!!'<span class = "tmpUNits">'.GENERAL::TotalEnrolledUnits().'</span> / '.$MaxLimit!!}</div>
              </div>
            </div>
          </div>

        </div>


        <hr>
        <div class="table-responsive">
            <form id = "frmEnrol">
              @csrf
              <input type = "hidden" name = "StudentNo" value = "{{Crypt::encryptstring($one->StudentNo)}}">
              <input type = "hidden" name = "RegistrationID" value = "{{Crypt::encryptstring($reg->RegistrationID)}}">
              <input type = "hidden" name = "AllowedUnits" value = "{{Crypt::encryptstring($MaxLimit)}}">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th class = "text-nowrap">CourseNo</th>
                    <th class = "text-nowrap">Descriptive Title</th>
                    <th class = "text-nowrap text-center">Units</th>
                    <th class = "text-nowrap">Schedule</th>
                    <th class = "text-nowrap">#</th>
                    <th class = "text-nowrap text-center"><small>Re-Ex</small></th>
                    <th class = "text-nowrap text-center"><small>FINAL</small></th>
                    <th class = "text-nowrap"><small>Pre-Req</small></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                      $oldsyear = '';
                      $oldSem = 0;
                      $ctrPropectos = 0;
                  ?>
                  @foreach($subjects as $subject)
                      <?php
                          $chk = '<div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                          <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                        </div>';
                          SCHEDULE::setId($subject->pri);
                      ?>
                      @if ($oldsyear != $subject->stud_year or $oldSem != $subject->semester)
                        <?php
                          $chkAll = '<div class="form-check form-switch">
                          <input class="chkAll form-check-input" type="checkbox" role="switch" value="'.$subject->stud_year.$subject->semester.'">
                          <label class="form-check-label" for="chk"'.$subject->stud_year.$subject->semester.'>All</label>
                          </div>';
                        ?>
                          <tr class = 'alert-success text-dark'>
                            <th>{!!$chkAll!!}</th>
                            <th colspan = "7">{{GENERAL::ProspectosLabel(['SchoolLevel'=>$SchoolLevel,'StudentYear'=>$subject->stud_year,'Semester'=>$subject->semester])}}</th>
                          </tr>
                      @endif
                      <?php
                        $sched = "";
                        $subjname = "";
                        $oldsyear = $subject->stud_year;
                        $oldSem = $subject->semester;
                      ?>

                      @if ($Major == $subject->major_in or empty($subject->major_in))
                      <?php
                        $prereqs = GENERAL::getPrerequisite($subjects, $subject->prerequisite);
                        $tmpG = GENERAL::getGradesinTMP($subject->id,$grades);
                        // dd($tmpG);
                        $inc = (isset($tmpG['inc'])?$tmpG['inc']:"");
                        $final = (isset($tmpG['final'])?$tmpG['final']:"");
                        $sy = (isset($tmpG['SchoolYear'])?$tmpG['SchoolYear']:"");
                        $sem = (isset($tmpG['Semester'])?$tmpG['Semester']:"");
                        $coursecode = (isset($tmpG['CourseCode'])?$tmpG['CourseCode']:"");
                        $Tym = (isset($tmpG['Time1'])?$tmpG['Time1']:"TBA").(isset($tmpG['Time2'])?(empty($tmpG['Time2'])?"":"<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$tmpG['Time2']):"");
                        $isC = GENERAL::isCredited($credits, $subject->id);
                        if ($isC){
                            $sched = "<span class = 'text-warning'><i class = 'bx bx-check'></i> Credited, no need to add</span>";
                        }else{
                          if (!empty($tmpG)){
                              //naa nay na enrol
                              $tmpStatus = GENERAL::isGradePass($final,$inc,$sy,$sem,(isset($tmpG['Finalize'])?$tmpG['Finalize']:""));
                              if ($sy==$SchoolYear && $sem==$Semester){
                                  $tmpStatus = "current";
                              }

                              if ($tmpStatus == "inc"){
                                  if (!empty($subject->prerequisite)){
                                      $tmpPre = explode(",", $subject->prerequisite);
                                      $ctr = 0;
                                      foreach ($tmpPre as $tmpPrevalue) {
                                          $tmpP = GENERAL::getGradesinTMP($tmpPrevalue,$grades);
                                          $Preinc = (isset($tmpP['inc'])?$tmpP['inc']:"");
                                          $Prefinal = (isset($tmpP['final'])?$tmpP['final']:"");

                                          $tmpStatus = GENERAL::isGradePass($Prefinal,$Preinc);

                                          if ($tmpStatus != "passed"){

                                              $ctr++;


                                          }
                                      }

                                      if ($ctr > 0){
                                          $tmpStatus = "prerequisite";
                                      }else{
                                          $tmpStatus = "inc";
                                      }
                                  }
                              }

                          }else{
                              //wla pa na enrol
                              $sched = "";

                              $tmpStatus = "enrol";
                              if (!empty($subject->prerequisite)){
                                  $tmpPre = explode(",", $subject->prerequisite);
                                  $ctr = 0;
                                  foreach ($tmpPre as $tmpPrevalue) {
                                      $tmpP = GENERAL::getGradesinTMP($tmpPrevalue,$grades);
                                      $Preinc = (isset($tmpP['inc'])?$tmpP['inc']:"");
                                      $Prefinal = (isset($tmpP['final'])?$tmpP['final']:"");

                                      $tmpStatus = GENERAL::isGradePass($Prefinal,$Preinc);

                                      if ($tmpStatus != "passed"){
                                          $cre = GENERAL::isCredited($credits, $tmpPrevalue);
                                          if (!$cre){
                                              $ctr++;
                                          }

                                      }
                                  }

                                  if ($ctr > 0){
                                      $tmpStatus = "prerequisite";
                                  }else{
                                      $tmpStatus = "enrol";
                                  }

                              }
                          }


                          switch($tmpStatus){
                              case "passed":
                                  $sched = "<span class = 'text-success'><i class = 'bx bx-check'></i> passed</span>";
                                  break;
                              case "current":
                                $chk = '<div class="form-check form-switch">
                                    <input disabled class="form-check-input" name = "selected[]" value = "'.Crypt::encryptstring($subject->pri).'" type="checkbox" role="switch" id="chk-'.$subject->pri.'">
                                    <label class="form-check-label" for="chk-'.$subject->pri.'">'.$subject->courseno.'</label>
                                  </div>';

                                  $subjname = "<input type = 'hidden' value = '".$subject->courseno."' name = 'subjname-".$subject->pri."'>";
                                  $sched = "<span class = 'text-success'><i class = 'bx bx-check'></i> (".$coursecode.") ".$Tym ."</span>";
                                  break;
                              case "inp":
                                  $sched = "<span class = 'text-success'><i class = 'bx bx-run'></i> IN PROGRESS</span>";
                                  break;
                              case "failed":
                                  $chk = '<div class="form-check form-switch">
                                    <input class="chk'.$subject->stud_year.$subject->semester.' form-check-input" name = "selected[]" value = "'.Crypt::encryptstring($subject->pri).'" type="checkbox" role="switch" id="chk-'.$subject->pri.'">
                                    <label class="form-check-label" for="chk-'.$subject->pri.'">'.$subject->courseno.'</label>
                                  </div>';

                                  $subjname = "<input type = 'hidden' value = '".$subject->courseno."' name = 'subjname-".$subject->pri."'>";
                                  if (!GENERAL::canEditEnrolment($reg->finalize)){
                                    $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> ENROLMENT MARKED AS FINAL</span>";
                                  if (auth()->user()->AllowSuper == 1){
                                      $sched = SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section);
                                      if (empty($sched)){
                                        $subjname = "";
                                        $chk = '<div class="form-check form-switch">
                                          <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                          <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                        </div>';
                                        $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> NO AVAILABLE SCHEDULE</span>";
                                      }
                                    }
                                  }else{
                                    $sched = SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section);
                                    if (empty($sched)){
                                      $subjname = "";
                                      $chk = '<div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                        <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                      </div>';
                                      $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> NO AVAILABLE SCHEDULE</span>";
                                    }
                                  }
                                  break;
                              case "prerequisite":
                                  $sched = "<span class = 'text-danger'><i class = 'bx bx-message-square-error'></i> Problem(s) with pre-requisite</span>";
                                  break;
                              case "inc":

                                  $chk = '<div class="form-check form-switch">
                                    <input class="chk'.$subject->stud_year.$subject->semester.' form-check-input" name = "selected[]" value = "'.Crypt::encryptstring($subject->pri).'" type="checkbox" role="switch" id="chk-'.$subject->pri.'">
                                    <label class="form-check-label" for="chk-'.$subject->pri.'">'.$subject->courseno.'</label>
                                  </div>';

                                  $subjname = "<input type = 'hidden' value = '".$subject->courseno."' name = 'subjname-".$subject->pri."'>";
                                  if (!GENERAL::canEditEnrolment($reg->finalize)){
                                    $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> ENROLMENT MARKED AS FINAL</span>";
                                    if (auth()->user()->AllowSuper == 1){
                                      $sched = SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section);
                                      if (empty($sched)){
                                        $subjname = "";
                                        $chk = '<div class="form-check form-switch">
                                          <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                          <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                        </div>';
                                        $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> NO AVAILABLE SCHEDULE</span>";
                                      }
                                    }
                                  }else{
                                    $sched = SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section);
                                    if (empty($sched)){
                                      $subjname = "";
                                      $chk = '<div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                        <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                      </div>';
                                      $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> NO AVAILABLE SCHEDULE</span>";
                                    }
                                  }


                                  break;
                              case "tograde":
                                  $sched = "<span class = 'text-dark'><i class = 'bx bx-error-circle'></i> To be graded</span>";
                                  break;
                              case "nograde":
                              case "notfinalize":
                              case "enrol":
                                  $chk = '<div class="form-check form-switch">
                                    <input class="chk'.$subject->stud_year.$subject->semester.' form-check-input" name = "selected[]" value = "'.Crypt::encryptstring($subject->pri).'" type="checkbox" role="switch" id="chk-'.$subject->pri.'">
                                    <label class="form-check-label" for="chk-'.$subject->pri.'">'.$subject->courseno.'</label>
                                  </div>';

                                  $subjname = "<input type = 'hidden' value = '".$subject->courseno."' name = 'subjname-".$subject->pri."'>";
                                  if (!GENERAL::canEditEnrolment($reg->finalize)){
                                    $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> ENROLMENT MARKED AS FINAL</span>";
                                    if (auth()->user()->AllowSuper == 1){
                                      $sched = SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section);
                                      if (empty($sched)){
                                        $subjname = "";
                                        $chk = '<div class="form-check form-switch">
                                          <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                          <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                        </div>';
                                        $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> NO AVAILABLE SCHEDULE</span>";
                                      }
                                    }
                                  }else{

                                    $sched = SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section);
                                    if (empty($sched)){
                                      $subjname = "";
                                      $chk = '<div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                        <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                      </div>';
                                      $sched = "<span class = 'text-danger'><i class = 'fa fa-close'></i> NO AVAILABLE SCHEDULE</span>";
                                    }

                                  }
                                  break;
                          }

                          if (!GENERAL::canEditEnrolment($reg->finalize)){
                            if (!auth()->user()->AllowSuper == 1){
                            $chk = '<div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled>
                                    <label class="form-check-label" for="flexSwitchCheckDisabled">'.$subject->courseno.'</label>
                                  </div>';
                            }

                          }
                        }
                      ?>
                      <tr>
                        <td class = "text-nowrap text-dark fw-bolder">{!!$chk!!} {!!$subjname!!}</td>
                        <td class = "text-nowrap">{{substr($subject->coursetitle,0,30)}}</td>
                        <td class = "text-nowrap text-center">{{($subject->exempt == 1?"(".$subject->units.")":$subject->units) . " (".$subject->lec."/".$subject->lab.")"}}</td>
                        <td class = "text-nowrap" id = "sched-{{$subject->pri}}">{!!$sched!!}</td>
                        <td class = "text-nowrap">{{GENERAL::countEnrolled($subject->id, $grades)}}</td>
                        <td class = "text-nowrap text-center"><small>{!! (empty($inc)?"":($inc=="0.0"?"":GENERAL::GradeRemarks($inc,1,'fw-bold'))) !!}</small></td>
                        <td class = "text-nowrap text-center" id = "final-{{$subject->pri}}"><small>{!!GENERAL::GradeRemarks($final,1,'fw-bold')!!}</small></td>
                        <td class = "text-nowrap"><small>{{$prereqs}}</small></td>
                      </tr>
                      @endif
                  @endforeach
                </tbody>
              </table>
              <!-- action button -->
              <div class="buy-now fab-container">
                <div class="fab shadow">
                  <div class="fab-content">
                    <i class="fa fa-navicon text-white fa-2x"></i>
                  </div>
                </div>

                <div class="sub-button shadow bg-info">
                  <a href="#" id = "btnSaveEnrol" target="_blank">
                    <i class="fa fa-floppy-o text-white fa-lg"></i>
                  </a>
                </div>
                <div class="sub-button shadow bg-primary">
                  <a href="#" id = "idCart" sid = "{{Crypt::encryptstring($one->StudentNo)}}" target="_blank" data-bs-toggle="modal" data-bs-target="#showCart">
                    <i class="fa fa-shopping-cart text-white fa-lg"></i>
                    @if (empty(GENERAL::countCurrentEnrolled()))
                    @else
                      <span class="badge badge-center rounded-pill bg-danger">{{GENERAL::countCurrentEnrolled()}}</span>
                    @endif
                  </a>
                </div>

              </div>
              <!-- close action button -->
            </form>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneSMSStudent" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-send"></i> Send SMS to {{ utf8_decode(strtoupper($one->FirstName . (empty($one->MiddleName)?' ':' '.$one->MiddleName[0].'. ') .$one->LastName)) }}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmSendOneSMS">
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">
        <input type = "hidden" name = "hidStudentID" id = "hidStudentID" value = "{{Crypt::encryptstring($one->StudentNo)}}">
        <div id = "onesms"></div>
        <label>Enter your message. (Limited to 155 per SMS)</label>
        <textarea name = "BulkMessage" class = "form-control mb-3" rows = "10" autofocus></textarea>
        <button type="button" id = "btnsendonesms" class="btn btn-primary mb-2  w-100">Send Now</button>
        <button type="button" class="btn btn-outline-secondary  w-100" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
</div>

<div class="modal fade" id="showCart" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel4">Enrolled subjects</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id = "bodyCart">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        @if(GENERAL::canEditEnrolment($reg->finalize))
        <button type="button" class="btn btn-primary" id = "btnFinalizeDepartment">Finalize</button>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="mdlSetStatus" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="backDropModalTitle">Set Student Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <hr class = "m-0">
      <div class="modal-body">
        <form id = "frmModalStudentStatus">
          <input type = "text" name = "hiddentStudentNo" hidden>
          <h3 id = "mdlName"></h3>
          <div class="row">
            <div class="col mb-3">
              <label for="StudentStatus" class="form-label">Select Status</label>
              <select class="form-select" id = "StudentStatus" name = "StudentStatus" placeholder="Select Status">
                  <option value = ""></option>
                  @foreach($statuss as $status)
                    <option value = "{{$status->status}}" <?=$status->status==$reg->StudentStatus?"Selected":""?>>{{$status->status}}</option>
                  @endforeach
              </select>
            </div>
          </div>
          <div class="row g-2">
            <div class="col mb-0">
              <label for="StudentYear" class="form-label">Student Year</label>
              <select class="form-select" id = "StudentYear" name = "StudentYear" placeholder="Select Year">
                  <option value = ""></option>
                  @for($x=1;$x<=12;$x++)
                    <option <?=$x==$reg->StudentYear?"Selected":""?>>{{$x}}</option>
                  @endfor
              </select>
            </div>
            <div class="col mb-0">
              <label for="StudentSection" class="form-label">Section</label>
              <input type="text" id="StudentSection" name = "StudentSection" value = "{{$reg->Section}}" class="form-control">
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <div id="hidError" class = "mb-0"></div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id = "btnEnrolmentProceed" class="btn btn-primary">Proceed</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/sms.js?id=20240419')}}"></script>
<script src="{{asset('storage/js/enrolment.js?id=20241225')}}"></script>
<script src="{{asset('storage/js/department.js?id=20240514a')}}"></script>
@include('slsu.enrol.chkjs')
@endsection
