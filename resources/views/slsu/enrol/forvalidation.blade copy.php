
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item ">
      <a class = "text-primary" href="{{route('step4list')}}">Manage Step 4</a>
    </li>
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
                        <div style = "font-size: 15px; font-weight: 600">{!!(empty($reg->TES)?"<span class = 'text-danger'>INVALID</span>":($reg->TES==1?"<span class = 'text-success'>FHE</span>":"<span class = 'text-danger'>NON-FHE</span>"))!!}</div>
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
                      <div style = "font-size: 15px; font-weight: 600">{!!date('F j, Y', strtotime($reg->DateEnrolled))!!}</div>
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
                          <td class = "text-nowrap">{{$sub->coursetitle}}</td>
                          <td class = "text-nowrap text-center">{{($sub->exempt==1?"(".$sub->units.")":$sub->units)}}</td>
                          <td class = "text-nowrap">{!!(empty($sub->Time1)?"":$sub->Time1).(empty($sub->Time2)?"":"<br>".$sub->Time2)!!}</td>
                        </tr>
                    @endforeach
                        <tr>
                          <td colspan = "3" class = "text-end">TOTAL UNITS:</td>
                          <td class = "text-nowrap text-center fw-bolder">{{$totalUnits}}</td>
                          <td class = "text-nowrap">&nbsp;</td>
                        </tr>
                      </tbody>
                  </table>

                  <div class="divider mt-4">
                    <div class="divider-text">Documents Required Checklist</div>
                  </div>

                  <div class = "alert alert-info">Kindly mark the checkbox corresponding to the submitted documents.</div>
                  <form id = "frmValidate">
                    @csrf
                    <input type = "hidden" value = "{{Crypt::encryptstring($reg->StudentNo)}}" name = "hidStudentNo">
                    @foreach($required as $req)

                      @if ($req->ForNonFHE == 1)
                          @if ($reg->TES == 2)
                          <?php
                            $ctrChk++
                          ?>
                          <div class="form-check form-switch">
                            <input class="form-check-input" name = "docrequired[]" type="checkbox" role="switch" id="chk-{{$req->id}}" value = "{{Crypt::encryptstring($req->id)}}">
                            <label class="form-check-label" for="chk-{{$req->id}}">{{$req->Description}}</label>
                          </div>
                          @endif
                      @else
                        @if ($req->LocalEnrolment==1)
                          @if (strtolower($reg->WhereEnrolled) == "walkin")
                          <?php
                            $ctrChk++
                          ?>
                          <div class="form-check form-switch">
                            <input class="form-check-input" name = "docrequired[]" type="checkbox" role="switch" id="chk-{{$req->id}}" value = "{{Crypt::encryptstring($req->id)}}">
                            <label class="form-check-label" for="chk-{{$req->id}}">{{$req->Description}}</label>
                          </div>
                          @endif
                        @else
                          <?php
                            $ctrChk++
                          ?>
                          <div class="form-check form-switch">
                            <input class="form-check-input" name = "docrequired[]" type="checkbox" role="switch" id="chk-{{$req->id}}" value = "{{Crypt::encryptstring($req->id)}}">
                            <label class="form-check-label" for="chk-{{$req->id}}">{{$req->Description}}</label>
                          </div>
                        @endif
                      @endif

                    @endforeach

                    @foreach($requested as $requestedChk)
                          <div class="form-check form-switch">
                            <input class="form-check-input" name = "docrequired[]" type="checkbox" role="switch" id="chk-{{$req->id}}" value = "{{Crypt::encryptstring($req->id)}}">
                            <label class="form-check-label" for="chk-{{$req->id}}">{{$petition->Description}} - {{$requestedChk['CourseNo']}}</label>
                          </div>
                    @endforeach
                    <input type = "hidden" value = "{{Crypt::encryptstring($ctrChk)}}" name = "hidCtr">
                  </form>
                  <button class = "mt-4 btn btn-primary" id = "btnValidate">RECEIVE</button>
              @endif
            @endif
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

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/sms.js?id=20240419')}}"></script>
<script src="{{asset('storage/js/enrolment.js?id=20240529a')}}"></script>
@endsection
