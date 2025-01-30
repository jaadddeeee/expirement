<?php
  use App\Http\Controllers\SLSU\GradeLock;
  $lock = new GradeLock(['sy' => $passsy, 'sem' => $passsem]);
  $lock->setTeacherID(auth()->user()->Emp_No);
  $lock->setSchedID($lists[0]->courseofferingid);
  $lock->isOKToEncode();
?>

@extends('layouts/contentNavbarLayout')

@section('title', 'My Class')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">My Class > </span> <span class="text-dark fw-light"><a href="javascript:history.back()"> List of students enrolled</a> ></span> {{(isset($subinfo->courseno)?$subinfo->courseno:"No enrolled")}}
</h4>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>
        </div>
        <div class="card-action">
            <button class = "btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBackdrop" aria-controls="offcanvasBackdrop"><i class = "fa fa-send-o"></i> Send SMS</button>
            @if($lock->isOKToEncode())
            <button class = "btn btn-sm btn-danger btnSaveGrades" type = "button"><i class = "fa fa-save"></i> Save Grades</button>
            @endif
            {!! $headerAction ?? '' !!}
        </div>
      </div>
      <div class="card-body">
          @if (count($lists) <= 0)
              {!! GENERAL::Error("No student enrolled") !!}
          @else
            @if (!empty(session('ErrorBlob')))
            {!! GENERAL::Error(session('ErrorBlob')) !!}
            @endif
            <div class = "row">
              <div class = 'col-xs-12 col-lg-7'>
                <div id = "bulksmsgrade"></div>
                <div class = "table-responsive">
                  <form id = "frmGrades">
                    <table class = 'table table-hover table-sm'>
                        <thead>
                            <tr>
                              <td>#</td>
                              <td>StudentNo</td>
                              <td>Name</td>
                              <td></td>
                              <td class = "text-center">MT</td>
                              <td class = "text-center">FT</td>
                              <td class = "text-center">AVG</td>
                              <td class = "text-center">INC</td>
                            </tr>
                        </thead>
                        <tbody>
                    @php
                        $male = 0;
                        $female = 0;
                        $validated = 0;
                        $done = 0;
                        $zero = 0;
                        $inc = 0;
                        $failed = 0;
                        $passed = 0;
                        $complied = 0;
                        $dropped = 0;

                        $gradequery = [];

                    @endphp
                    @foreach($lists as $enrolled)

                        @if ($enrolled->finalize == 1)
                        @php
                          $validated++;

                          if ($enrolled->Sex == "M")
                            $male++;
                          else
                            $female++;

                          if (empty($enrolled->final))
                            $zero++;
                          else
                            $done++;

                          if ($enrolled->final == 8.8)
                            $inc++;

                          if ($enrolled->final == 5)
                            $failed++;

                          if ($enrolled->final > 0  and $enrolled->final <= 3)
                            $passed++;

                          if (!empty($enrolled->inc))
                            $complied++;

                          if ($enrolled->final == 9.9)
                            $dropped++;

                        @endphp
                            <tr>
                              <td class = 'text-nowrap'>{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                              <td class = 'text-nowrap'><a class = "showonestudent" href = "#" aname = "{{ucwords(strtolower(utf8_decode($enrolled->LastName . ', '. $enrolled->FirstName)))}}" sid = "{{Crypt::encryptstring($enrolled->StudentNo)}}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasperStudent" aria-controls="offcanvasBackdrop">{{$enrolled->StudentNo}}</a></td>
                              <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($enrolled->LastName . ', '. $enrolled->FirstName)))}}</td>
                              <td><span class = 'clsStatus' id = "msg-{{$enrolled->id.'_'.$passsy.'_'.$passsem.'_'.$enrolled->StudentNo}}"></span></td>
                              <td class = 'text-nowrap text-center'>
                              <input type = "hidden" name = "flag[]" value = "{{Crypt::encryptstring($enrolled->id.'_'.$passsy.'_'.$passsem.'_'.$enrolled->StudentNo)}}" style = "width: 50px; text-align: center">
                                  <?php
                                        $ff = $enrolled->final;
                                        if ($ff == "0.0")
                                          $ff = 0;
                                  ?>
                                @if($lock->isOKToEncode())
                                  @if (!empty($ff))
                                      {!! GENERAL::GradeRemarks($enrolled->midterm, 1) !!}
                                  @else
                                      <input type = "number" name = "mt-{{$enrolled->id.'_'.$passsy.'_'.$passsem.'_'.$enrolled->StudentNo}}" pref = "{{$enrolled->id.'-'.$passsy.'-'.$passsem}}" id = "mt-{{$enrolled->id.'-'.$passsy.'-'.$passsem}}" button = "mt" class = "mt-{{$enrolled->StudentNo}} validgrades" value = "{{(empty($enrolled->midterm)?'':($enrolled->midterm=='0.0'?'':$enrolled->midterm))}}" style = "width: 50px; text-align: center">
                                  @endif
                                @else
                                  {!! GENERAL::GradeRemarks($enrolled->midterm, 1) !!}
                                @endif
                              </td>
                              <td class = 'text-nowrap text-center'>
                                @if($lock->isOKToEncode())
                                  @if (!empty($ff))
                                    {!! GENERAL::GradeRemarks($enrolled->finalterm, 1) !!}
                                  @else
                                      <input type = "number" name = "ft-{{$enrolled->id.'_'.$passsy.'_'.$passsem.'_'.$enrolled->StudentNo}}" pref = "{{$enrolled->id.'-'.$passsy.'-'.$passsem}}" id = "ft-{{$enrolled->id.'-'.$passsy.'-'.$passsem}}" button = "ft" class = "ft-{{$enrolled->StudentNo}} validgrades"  value = "{{(empty($enrolled->finalterm)?'':($enrolled->finalterm=='0.0'?'':$enrolled->finalterm))}}" style = "width: 50px; text-align: center">
                                  @endif
                                @else
                                  {!! GENERAL::GradeRemarks($enrolled->finalterm, 1) !!}
                                @endif
                                </td>
                              <td class = 'text-nowrap text-center'>
                                @if($lock->isOKToEncode())
                                  @if (!empty($ff))
                                    {!! GENERAL::GradeRemarks($enrolled->final, 1, 'fw-bold') !!}
                                  @else
                                      <input type = "number" name = "f-{{$enrolled->id.'_'.$passsy.'_'.$passsem.'_'.$enrolled->StudentNo}}" pref = "{{$enrolled->id.'-'.$passsy.'-'.$passsem}}" id = "f-{{$enrolled->id.'-'.$passsy.'-'.$passsem}}" button = "f" class = "f-{{$enrolled->StudentNo}} validgrades"  value = "{{(empty($enrolled->final)?'':($enrolled->final=='0.0'?'':$enrolled->final))}}" style = "width: 50px; text-align: center">
                                  @endif
                                @else
                                  {!! GENERAL::GradeRemarks($enrolled->final, 1, 'fw-bold') !!}
                                @endif

                              </td>
                              <td class = 'text-nowrap text-center'>{!! (empty($enrolled->inc)?"":($enrolled->inc=='0.0'?'':GENERAL::GradeRemarks($enrolled->inc, 1))) !!}</td>
                            </tr>
                        @endif

                    @endforeach
                        </tbody>
                    </table>
                  </form>
                </div>
              </div>
              <div class = 'col-xs-12 col-lg-5'>

                  <div class = "mt-1 ml-5 mt-2">
                      <h5>LEGEND:</h5>
                      <div>9.9 = <strong>Dropped</strong></div>
                      <div>8.8 = <strong>Incomplete</strong></div>
                      <div>7.7 = <strong>Passed</strong></div>
                      <div>6.6 = <strong>In-progress</strong></div>
                      <div>5.0 = <strong>Failed</strong></div>
                      <div>1.0 - 3.0 = <strong>Passing grades</strong></div>
                      <div>3.1 - 3.5 = <strong>Cond'l Grades (MT Only)</strong></div>
                      <hr>
                      <h5>SUMMARY</h5>
                      <div>Total registered: <strong>{{count($lists)}}</strong></div>
                      <div>Total validated: <strong>{{$validated}}</strong></div>
                      <div>With grades: <strong>{{$done}}</strong></div>
                      <div class = 'text-danger'>Without grades: <strong>{{$zero}}</strong></div>
                      <div class = 'text-danger'>INC: <strong>{{$inc}}</strong></div>
                      <div class = 'text-success'>PASSED: <strong>{{$passed}}</strong></div>
                      <div class = 'text-danger'>FAILED: <strong>{{$failed}}</strong></div>
                      <div class = 'text-danger'>COMPLIED: <strong>{{$complied}}</strong></div>
                      <div class = 'text-danger'>DROPPED: <strong>{{$dropped}}</strong></div>
                      <hr>
                      <h5>PERIOD</h5>
                      @php

                      @endphp
                      <div>School Year: <strong>{{GENERAL::setSchoolYearLabel($passsy,$passsem)}}</strong></div>
                      <div>Semester: <strong>{{GENERAL::Semesters()[$passsem]['Long']}}</strong></div>
                      <div>Encoding Starts: <strong>{{(empty($lock->getDateStart())?": NOT SET":date('F j, Y', strtotime($lock->getDateStart())))}}</strong></div>
                      <div>Encoding Ends: <strong>
                          @if (empty($lock->getDateEnd()))
                            NOT SET
                          @else
                            {{ date('F j, Y', strtotime($lock->getDateEnd())) }}
                          @endif</strong>
                      </div>
                      <div>Remaining: <strong>
                          @if (date('Y-m-d')==$lock->getDateEnd())
                            0
                          @elseif (date('Y-m-d')<$lock->getDateEnd())
                            {{Carbon\Carbon::now()->diffInDays($lock->getDateEnd())+1}} day(s)
                          @else
                            NOT SET
                          @endif
                            </strong></div>
                      <div>Status: <strong>
                            @if($lock->isOKToEncode())
                              <span class = 'text-success'><i class = ' fa fa-unlock-alt'></i> OPEN</span>
                            @else
                              <span class = 'text-danger '><i class = 'fa fa-lock'></i> CLOSED</span>
                            @endif</strong></div>
                      <hr>
                      <h5>SUBJECT INFO</h5>
                      <div>Male: <strong>{{$male}}</strong></div>
                      <div>Female: <strong>{{$female}}</strong></div>
                      <div>Course No: <strong>{{$subinfo->courseno}}</strong></div>
                      <div>Description: <strong>{{$subinfo->coursetitle}}</strong></div>
                      <div>Course Code: <strong>{{$subinfo->coursecode}}</strong></div>
                      <div>Schedule: <strong>{{$lists[0]->Time1 . (!empty($lists[0]->Time2)?" and ".$lists[0]->Time2:"")}}</strong></div>
                      <hr>
                      <button class = "mt-1 btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBackdrop" aria-controls="offcanvasBackdrop"><i class = "fa fa-send-o"></i> Send SMS</button>
                      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasBackdrop" aria-labelledby="offcanvasBackdropLabel">
                        <div class="offcanvas-header">
                          <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-send"></i> Send Bulk SMS</h5>
                          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <form id = "frmSendBulkSMS">
                          <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                            <input type = "hidden" name = "hidID" value = "{{Crypt::encryptstring($ID)}}">
                            <div id = "bulksms"></div>
                            <label>Enter your message. (Limited to 155 per SMS)</label>
                            <textarea name = "BulkMessage" class = "form-control mb-3" rows = "10" autofocus></textarea>
                            <button type="button" id = "sendbulksms" class="btn btn-primary mb-2 w-100"><i class = "fa fa-send-o"></i> Send Now</button>
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="offcanvas">Cancel</button>
                          </div>
                        </form>
                      </div>
                      <a id = "gradesheet" filename = "{{$subinfo->courseno.'-'.$lists[0]->coursecode}}.pdf" href = "#" sid = "{{Crypt::encryptstring($ID)}}" sy = "{{Crypt::encryptstring($passsy)}}" sem = "{{Crypt::encryptstring($passsem)}}" class = "mt-1 btn btn-sm btn-primary"><i class = "bx bx-download"></i> Generate Grade Sheet</a><br>
                      @if($lock->isOKToEncode())
                      <button class = "mt-1 btn btn-sm btn-info btnUploadClassRecord" type = "button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneUploadClassRecord" aria-controls="offcanvasBackdrop"><i class = "fa fa-upload"></i> Import from Class Record</button><br>
                      <button class = "mb-2 mt-1 btn btn-sm btn-danger btnSaveGrades" type = "button"><i class = "fa fa-save"></i> Save Grades</button>
                      @endif
                      @if (!empty(session('ErrorBlob')))
                      {!! "<br>".GENERAL::Error(session('ErrorBlob')) !!}
                      @endif
                      <?php
                      session(['ErrorBlob'=> ""]);
                      ?>
                  </div>

              </div>
            </div>
          @endif
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasperStudent" aria-labelledby="offcanvasBackdropLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-user"></i> Student Information</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div>
    <div class="offcanvas-body my-auto mx-0 flex-grow-0">

      <div id = "off-one-student"></div>

    </div>
  </div>

</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneSMSStudent" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-send"></i> <span id = "smsnamereceiver"></span></h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmSendOneSMS">
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">
        <input type = "hidden" name = "hidStudentID" id = "hidStudentID">
        <div id = "onesms"></div>
        <label>Enter your message. (Limited to 155 per SMS)</label>
        <textarea name = "BulkMessage" class = "form-control mb-3" rows = "10" autofocus></textarea>
        <button type="button" id = "btnsendonesms" class="btn btn-primary mb-2 w-100">Send Now</button>
        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneUploadClassRecord" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-upload"></i> Upload Class Record</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmImportBulkGrades" enctype="multipart/form-data">
    <div class="offcanvas-body my-auto mx-0 flex-grow-0">
      <input type = "hidden" name = "hidID" value = "{{Crypt::encryptstring($ID)}}">
      <input type = "hidden" name = "hidTable" value = "{{Crypt::encryptstring('grades'.$passsy.$passsem)}}">
      <input type = "hidden" name = "hidTotalRows" value = "{{Crypt::encryptstring($validated)}}">

      <div id = "bulksmsgradeside"></div>
      <label for="fileexcel" class="form-label">Select the excel file to import <span class = "text-danger">*</span></label>
      <input class="form-control mb-2" name = "fileexcel" id="fileexcel" type="file" accept = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
      <div class = "alert alert-info">
        Upload same file that was generated from My Class menu. This will automatically fill the textboxes with the Excel data. You can check the entries after importing and then click Save Grades. After saving, students will receive SMS notifications.
      </div>
        <button type="submit" id = "uploadfile" class="btn btn-primary mb-2 w-100"><i class = "fa fa-send-o"></i> Import Now</button>
        <button type="button" class="btn btn-outline-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>

        <div class = "alert alert-info mt-2">
          STATUS AFTER IMPORT<br>
          <div id = "tr"></div>
          <div id = "ti"></div>
          <div id = "te"></div>
        </div>
      </div>
    </form>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/compute.js?id=20230825')}}"></script>
<script src="{{asset('storage/js/teacher.js?id=20230822')}}"></script>
<script src="{{asset('storage/js/grades.js?id=20231223')}}"></script>
<script src="{{asset('storage/js/report.js?id=20231130')}}"></script>
@endsection
