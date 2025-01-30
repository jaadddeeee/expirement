<?php
  use Carbon\Carbon;
?>
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">Record</a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">{{$pageTitle}}</a>
    </li>
  </ol>
</nav>
<?php
  $bdate = $one->BirthDate;
  $age = 0;
  if (!empty($bdate)){
    $tmp = explode(" ", $bdate);
    $bdate = $tmp[2]."-".str_pad($tmp[0],2,"0", STR_PAD_LEFT)."-".str_pad($tmp[1],2,"0", STR_PAD_LEFT);
    $age = Carbon::parse($bdate)->age;;
  }
?>
<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <input hidden type = "text" value = "{{Crypt::encryptstring($one->StudentNo)}}" id = "hiddenStudentNo">
          <input hidden type = "text" value = "{{Crypt::encryptstring($campus)}}" id = "hiddenCampus">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}} {!!($one->Sex=="M"?'<i class = "fa fa-male text-primary"></i>':'<i class = "fa fa-female text-danger"></i>')!!}</h4>
          <label>{{$one->StudentNo}} / {{$one->accro." (".$one->cur_num.")"}}{{(empty($one->course_major)?"":" / ".$one->course_major)}}</label><br>
          <label><i class = "fa fa-mobile"></i> {!!(empty($one->ContactNo)?"No registered mobile":'<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">SMS '.$one->ContactNo.'</a>')!!} <i class = "fa fa-send-o"></i> {!!(empty($one->email)?"No e-mail registered":'<a href = "mailto:'.$one->email.'">'.$one->email.'</a>')!!}</label><br>
          <label>{{$age}} years old
        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}
        </div>
      </div>
    </div>

    <div class="nav-align-top mb-4 mt-3">
      <ul class="nav nav-tabs nav-fill" role="tablist">

        <li class="nav-item">
          <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-profile" aria-controls="navs-justified-profile" aria-selected="false"><i class="tf-icons bx bx-user"></i> Profile</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link tabGrades" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-grades" aria-controls="navs-justified-grades" aria-selected="true"><i class="tf-icons bx bxl-google-plus"></i> Grades</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link tabEducation" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-education" aria-controls="navs-justified-education" aria-selected="true"><i class="tf-icons bx bxs-graduation"></i> Educational Background</button>
        </li>
      </ul>
      <div class="tab-content">

        <div class="tab-pane fade active show" id="navs-justified-profile" role="tabpanel">
          <!-- Account -->
          <div class="card-body">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
              <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt class="d-block rounded" height="100" width="100" >
              <div class="button-wrapper">
                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                  <span class="d-none d-sm-block">Upload new photo</span>
                  <i class="bx bx-upload d-block d-sm-none"></i>
                  <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
                </label>
                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                  <i class="bx bx-reset d-block d-sm-none"></i>
                  <span class="d-none d-sm-block">Reset</span>
                </button>

                <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
              </div>
            </div>
          </div>
          <hr class="my-0">
          <div class="card-body">

            <form id="formAccountSettings" method="POST" onsubmit="return false">
              <div class="row">
                <div class="mb-3 col-md-4">
                  <label for="FirstName" class="form-label" >First Name</label>
                  <input class="form-control" type="text" id="FirstName" name = "FirstName" value = "{{utf8_decode($one->FirstName)}}" autofocus />
                </div>
                <div class="mb-3 col-md-4">
                  <label for="MiddleName" class="form-label" >Middle Name</label>
                  <input class="form-control" type="text" id="MiddleName" name = "MiddleName" value = "{{utf8_decode($one->MiddleName)}}" />
                </div>
                <div class="mb-3 col-md-4">
                  <label for="LastName" class="form-label" >Last Name</label>
                  <input class="form-control" type="text" id="LastName" name = "LastName" value = "{{utf8_decode($one->LastName)}}" />
                </div>
                <div class="mb-3 col-md-4">
                  <label for="BirthDate" class="form-label">Birth Date</label>
                  <input type="date" class="form-control" id="BirthDate" name="BirthDate" value="{{$bdate}}" />
                </div>
                <div class="mb-3 col-md-8">
                  <label class="form-label" for="Birth Place">Birth Place</label>
                  <input type="text" id="BirthPlace" name="BirthPlace" class="form-control" value="{{$one->BirthPlace}}" />
                </div>



              </div>

            </form>
          </div>
          <!-- /Account -->
        </div>
        <div class="tab-pane fade " id="navs-justified-grades" role="tabpanel">
            <div id = "tabGrades"></div>
        </div>

        <div class="tab-pane fade " id="navs-justified-education" role="tabpanel">
            <div class="table-responsive">
              <table class = "table">
                  <tr>
                    <th colspan = 2 class = "text-nowrap text-primary fw-bolder">ELEMENTARY</th>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL NAME</th>
                    <td class = "text-nowrap">{{$one->elem_school}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL ADDRESS</th>
                    <td class = "text-nowrap">{{$one->elem_add}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">YEAR GRADUATED</th>
                    <td class = "text-nowrap">{{$one->elem_year}}</td>
                  </tr>

                  <tr>
                    <th colspan = 2 class = "text-nowrap text-primary fw-bolder">HIGH SCHOOL</th>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL NAME</th>
                    <td class = "text-nowrap">{{$one->high_school}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL ADDRESS</th>
                    <td class = "text-nowrap">{{$one->high_add}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">YEAR GRADUATED</th>
                    <td class = "text-nowrap">{{$one->high_year}}</td>
                  </tr>

                  <tr>
                    <th colspan = 2 class = "text-nowrap text-primary fw-bolder">SENIOR HIGH SCHOOL</th>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL NAME</th>
                    <td class = "text-nowrap">{{(isset($onesub->shigh_school)?$onesub->shigh_school:"")}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL ADDRESS</th>
                    <td class = "text-nowrap">{{(isset($onesub->shigh_add)?$onesub->shigh_add:"")}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">YEAR GRADUATED</th>
                    <td class = "text-nowrap">{{(isset($onesub->shigh_year)?$onesub->shigh_year:"")}}</td>
                  </tr>

                  <tr>
                    <th colspan = 2 class = "text-nowrap text-primary fw-bolder">COLLEGE</th>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL NAME</th>
                    <td class = "text-nowrap">{{$one->col_school}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">SCHOOL ADDRESS</th>
                    <td class = "text-nowrap">{{$one->col_add}}</td>
                  </tr>
                  <tr>
                    <th class = "text-nowrap">YEAR GRADUATED</th>
                    <td class = "text-nowrap">{{(empty($one->col_year)?"":$one->col_year)}}</td>
                  </tr>

              </table>
            </div>
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
<script src="{{asset('storage/js/sms.js?id=20240419')}}"></script>
@include('slsu.super.js')
@endsection
