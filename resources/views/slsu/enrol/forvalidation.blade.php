
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    @if(ROLE::isRegistrar() or auth()->user()->AllowSuper == 1)
    <li class="breadcrumb-item ">
      <a class = "text-primary" href="{{route('step4list')}}">Manage Step 5</a>
    </li>
    @endif
    <li class="breadcrumb-item active">
      <a href="javascript:void(0);">{{$pageTitle}}</a>
    </li>
  </ol>
</nav>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header">
          <div class="table-responsive">
            <input hidden type = "text" value = "{{Crypt::encryptstring($one->StudentNo)}}" id = "hidStudentNo">
            <div class="row">
              <div class="col-sm-12 col-lg-6 col-md-6">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                  <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                  <div class="button-wrapper">
                      <div style = "font-size: 20px; font-weight: 600">{{strtoupper($one->LastName .', '.$one->FirstName. (empty($one->MiddleName)?'':' '.$one->MiddleName[0].'.'))}}</div>
                      <small>{{$reg->course->accro." (".$CurNum.")"}}</small> {{(!empty($reg->major->course_major)?" | ".$reg->major->course_major:"")}}<br>
                      <small><i class = "fa fa-send"></i> {!!(empty($one->ContactNo)?"No registered mobile":'<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">SMS '.$one->StudentNo.'</a>')!!} | {!!(empty($one->email)?"No e-mail registered":'<a href = "mailto:'.$one->email.'">'.$one->email.'</a>')!!}</small><br>
                      <small class ='text-danger'>Record for SY {{GENERAL::setSchoolYearLabel($reg->SchoolYear, $reg->Semester)}} - {{GENERAL::Semesters()[$reg->Semester]['Long']}}</small>
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

                      <small>Max Allowed Units</small><br>
                      <div style = "font-size: 15px; font-weight: 600">{!!$MaxLimit!!}</div>
                  </div>
                </div>
              </div>

            </div>

            <div class = "row mt-3">
              <div class="col-sm-6 col-lg-3 col-md-3">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                  <div class="button-wrapper">
                      <small>FHE Status</small><br>
                      @if ($reg->SchoolLevel == "Under Graduate")
                        <div style = "font-size: 15px; font-weight: 600">{!!(empty($reg->TES)?"<span class = 'text-danger'>INVALID</span>":($reg->TES==1?"<span class = 'text-success'>FHE</span>":"<span class = 'text-danger'>NON-FHE</span>")."<div class = 'small'>by: ".$reg->TESBy."</div>")!!}</div>
                      @else
                        <div style = "font-size: 15px; font-weight: 600">N/A</div>
                      @endif


                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-lg-3 col-md-3">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                  <div class="button-wrapper">
                      <small>Mode Enrolment</small><br>
                      <div style = "font-size: 15px; font-weight: 600" class = "text-primary">{!!(empty($reg->WhereEnrolled)?"WALKIN":strtoupper($reg->WhereEnrolled))!!}</div>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-lg-3 col-md-3">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                  <div class="button-wrapper">
                      <small>Date Finalized</small><br>
                      <div style = "font-size: 15px; font-weight: 600">{!!(empty($reg->DateEnrolled)?"":date('F j, Y', strtotime($reg->DateEnrolled)))!!}</div>
                      <div class = 'small'>by: {{$reg->EnrollingOfficer}}</div>
                  </div>
                </div>
              </div>
              @if($reg->finalize == 1)
              <div class="col-sm-6 col-lg-3 col-md-3">
                <div class="alert alert-success">
                    <strong>VALIDATED</strong><br>
                    <div style = "font-size: 15px;">{!!date('F j, Y', strtotime($reg->DateValidated))!!}</div>
                    <div class = "small">by: {{$reg->ValidatedBy}}</div>
                </div>
              </div>
              @endif
            </div>
            <div class="divider">
              <div class="divider-text">Enrolled Subject (s)</div>
            </div>
            @if (!empty(session('ErrorBlob')))
            {!! "<br>".GENERAL::Error(session('ErrorBlob')) !!}
            @endif
            <?php
            session(['ErrorBlob'=> ""]);
            ?>

            <div id = "CartMsg"></div>
            @if (empty($reg))
              <div class = 'alert alert-danger'>Not enrolled this sem</div>
            @else
              @if ($reg->subjects()->count() <= 0)
                <div class = 'alert alert-danger'>No enrolled subject</div>
              @endif
                <table class = "table table-sm">
                    <thead>
                      <tr>

                        <td class = "text-nowrap">CourseCode</td>
                        <td class = "text-nowrap">CourseNo</td>
                        <td class = "text-nowrap">Description</td>
                        <td class = "text-nowrap text-center">Units</td>
                        <td class = "text-nowrap">Schedule</td>
                        <td class = "text-nowrap">Instructor</td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $totalUnits=0;
                        $requested = [];
                        $ctrChk = 0;
                      ?>
                  @foreach($subjects as $sub)
                      <?php

                          $totalUnits+=($sub->exempt==1?0:$sub->units);
                          if ($sub->RequireReqForm==1){
                            $ctrChk++;
                            array_push($requested, [
                              'CourseNo' => $sub->courseno
                            ]);
                          }
                      ?>
                      <tr id = "row-{{$sub->id}}">
                        <td class = "text-nowrap">{{$sub->coursecode}}</td>
                        <td class = "text-nowrap">{{$sub->courseno}}</td>
                        <td class = "text-nowrap">{!!wordwrap($sub->coursetitle, 60, '<br>')!!}</td>
                        <td class = "text-nowrap text-center">{{($sub->exempt==1?"(".$sub->units.")":$sub->units)}}</td>
                        <td class = "text-nowrap">{!!(empty($sub->Time1)?"":$sub->Time1).(empty($sub->Time2)?"":"<br>".$sub->Time2)!!}</td>
                        <td class = "text-nowrap">{{$sub->LastName.', '.$sub->FirstName}}</td>
                      </tr>
                  @endforeach
                      <tr>
                        <td colspan = "3" class = "text-end">TOTAL UNITS:</td>
                        <td class = "text-nowrap text-center fw-bolder">{{$totalUnits}}</td>
                        <td class = "text-nowrap">&nbsp;</td>
                      </tr>
                    </tbody>
                </table>

                <form id = "frmValidate">
                  @csrf
                  <input type = "hidden" value = "{{Crypt::encryptstring($reg->StudentNo)}}" name = "hidStudentNo">
                </form>
                @if($reg->finalize == 5 and (ROLE::isRegistrar() or auth()->user()->AllowSuper == 1))
                <button class = "mt-4 btn btn-primary" id = "btnValidate">RECEIVE</button>
                @elseif($reg->finalize == 1 and (ROLE::isRegistrar() or auth()->user()->AllowSuper == 1))
                <div class="btn-group mt-4">
                  <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="mdi mdi-file-pdf-box"></span> GENERATE
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('generate-orf-assessment', ['id' => Crypt::encryptstring($reg->RegistrationID)]) }}">ORF and Assessment</a></li>
                    <li><a class="dropdown-item" href="{{ route('generate-route-slip', ['id' => Crypt::encryptstring($reg->RegistrationID)]) }}">Route Slip</a></li>
                    <li><a class="dropdown-item" href="{{ route('generate-data-privacy', ['id' => Crypt::encryptstring($reg->RegistrationID)]) }}">Data Privacy Consent Form</a></li>
                    <li><a class="dropdown-item" href="{{ route('enrolment-form', ['id' => Crypt::encryptstring($reg->RegistrationID)]) }}">Enrolment Form</a></li>
                  </ul>
                </div>
                @endif
                <?php
                  $show = true;
                  // @if (count(Auth::user()->role) > 1)
                  if (ROLE::isDepartment() and $reg->finalize == 1){
                    $show = false;
                  }

                  if (ROLE::isRegistrar()){
                    $show = true;
                  }

                  if(auth()->user()->AllowSuper == 1){
                    $show = true;
                  }
                ?>
                @if ($show)
                <div class="btn-group mt-4">
                  <button type="button" class="ms-2 btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="mdi mdi-gesture-double-tap"></span> ACTIONS
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-bs-toggle = "modal" data-bs-target = "#modalAddSubject">Add subject</a></li>
                    @if ($reg->subjects()->count() > 0)
                    <li><a class="dropdown-item" href="#" data-bs-toggle = "modal" data-bs-target = "#modalModifySubject">Change schedule</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle = "modal" data-bs-target = "#modalDropSubject">Drop subject</a></li>
                    @endif
                    @if(auth()->user()->AllowSuper == 1 or ROLE::isRegistrar())
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="withdrawenrolment dropdown-item text-danger" href= "#" regid="{{ Crypt::encryptstring($reg->RegistrationID) }}">Withdraw enrolment</a></li>
                    @endif
                  </ul>
                </div>
                @endif
                @if(auth()->user()->AllowSuper == 1)
                <a class = "ms-2 mt-4 btn btn-info" href = "{{route('pro-enrolment',['id' => Crypt::encryptstring($reg->StudentNo)])}}">VIEW ENROLLMENT</a>
                @endif
            @endif
          </div>
        </div>
      </div>
  </div>
</div>

<div class="modal fade" id="modalModifySubject" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel4">Modify Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id = "frmModifySubject">
            <div class="row">
              <div class="modifymsg"></div>
                @csrf
                <input type = "hidden" value = "{{Crypt::encryptstring($reg->RegistrationID)}}" name = "hidRegistrationID">
                <div class="form-group">
                  <label for="ModifySubjects">Select subject <span class = 'text-danger'>*</span></label>
                  <select name="ModifySubjects" id="ModifySubjects" class="form-select">
                    <option value = "0"></option>
                    @foreach($subjects as $subj)
                      <option value = "{{$subj->pri}}">{{$subj->courseno}} - {{$subj->coursetitle}}</option>
                    @endforeach
                  </select>
                </div>

            </div>

            <div class="row mt-2">
              <div class="form-group">
                <label for="ModifySchedule">Select schedule</label>
                <div class="resModifySelect">
                  <select name="ModifySchedule" id="ModifySchedule" class="form-select">
                    <option value = "0"></option>
                  </select>
                </div>
              </div>
            </div>
            @if(ROLE::isRegistrar() or auth()->user()->AllowSuper == 1)
            <div class="divider">
              <div class="divider-text">OR ENTER COURSECODE</div>
            </div>

            <div class="row">
              <div class="form-group">

                <input type = "text" name="ModifyCourseCode" id="ModifyCourseCode" class="form-control">

              </div>
            </div>
            @endif
            <div class="divider">
              <div class="divider-text">REASON FOR CHANGING <span class = 'text-danger'>*</span></div>
            </div>
            <div class="row">
              <div class="form-group">
                <textarea rows = "5" name="ReasonModify" id="ReasonModify" class="form-control"></textarea>
              </div>
            </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id = "btnModifyManualSubject">Change</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDropSubject" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel4">Drop Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id = "frmDropSubject">
            <div class="row">
              <div class="dropmsg"></div>
                @csrf
                <input type = "hidden" value = "{{Crypt::encryptstring($reg->RegistrationID)}}" name = "hidRegistrationID">
                <div class="form-group">
                  <label for="AddSubjects">Select subject <span class = 'text-danger'>*</span></label>
                  <select name="DropSubject" id="DroSubject" class="form-select">
                    <option value = "0"></option>
                    @foreach($subjects as $subj)
                      <option value = "{{Crypt::encryptstring($subj->id)}}">{{$subj->courseno}} - {{$subj->coursetitle}}</option>
                    @endforeach
                  </select>
                </div>

            </div>

            <div class="divider">
              <div class="divider-text">REASON FOR DROPPING <span class = 'text-danger'>*</span></div>
            </div>
            <div class="row">
              <div class="form-group">
                <textarea rows = "5" name="ReasonDrop" id="ReasonDrop" class="form-control"></textarea>
              </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id = "btnDropManualSubject">Drop</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAddSubject" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel4">Add Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id = "frmAddSubject">
            <div class="row">
              <div class="addmsg"></div>

                @csrf
                <input type = "hidden" value = "{{Crypt::encryptstring($reg->RegistrationID)}}" name = "hidRegistrationID">
                <div class="form-group">
                  <label for="AddSubjects">Select subject <span class = 'text-danger'>*</span></label>
                  <select name="AddSubjects" id="AddSubjects" class="form-select">
                    <option value = "0"></option>
                      <?php
                        $sem = 0;
                        $studentyear = 0;
                      ?>
                      @foreach($addsubjects as $subj)

                        @if($sem != $subj->semester or $studentyear != $subj->stud_year)
                          <?php
                            $sem = $subj->semester;
                            $studentyear = $subj->stud_year;
                            // dd($subj->stud_year);
                          ?>
                          <optgroup label="{{GENERAL::YearStanding()[$subj->stud_year]['Long'].' - '.GENERAL::Semesters()[$sem]['Long']}}">

                        @endif
                        <option value = "{{$subj->pri}}">{{$subj->courseno}} - {{$subj->coursetitle}}</option>
                        @if($sem != $subj->semester or $studentyear != $subj->stud_year)
                        </optgroup>
                        @endif
                      @endforeach
                  </select>
                </div>

            </div>

            <div class="row mt-2">
              <div class="form-group">
                <label for="AddSchedule">Select schedule</label>
                <div class="resAddSelect">
                  <select name="AddSchedule" id="AddSchedule" class="form-select">
                    <option value = "0"></option>
                  </select>
                </div>
              </div>
            </div>
            @if(ROLE::isRegistrar() or auth()->user()->AllowSuper == 1)
            <div class="divider">
              <div class="divider-text">OR ENTER COURSECODE</div>
            </div>

            <div class="row">
              <div class="form-group">

                <input type = "text" name="AddCourseCode" id="AddCourseCode" class="form-control">

              </div>
            </div>
            @endif
            <div class="divider">
              <div class="divider-text">REASON FOR ADDING <span class = 'text-danger'>*</span></div>
            </div>
            <div class="row">
              <div class="form-group">
                <textarea rows = "5" name="ReasonAdd" id="ReasonAdd" class="form-control"></textarea>
              </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>

        <button type="button" class="btn btn-primary" id = "btnAddManualSubject">Add</button>

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

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/sms.js?id=20240419')}}"></script>
@include('slsu.enrol.js')
@endsection
