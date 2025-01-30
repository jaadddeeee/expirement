<?php
    $gGrowth = 0;

    if (ROLE::isTeacher()){
        $cGrade = (isset($countGrade->cGrade)?$countGrade->cGrade:0);
        $cNoGrade = (isset($countNoGrade->cGrade)?$countNoGrade->cGrade:0);
        $total = $cGrade + $cNoGrade;
        if (!empty($total) and !empty($cGrade)){
          $gGrowth = ($cGrade / $total) * 100;
          if ($gGrowth > 0 and $gGrowth <= 1){
            $gGrowth = 1;
          }else{
            $gGrowth = round($gGrowth);
          }

        }
    }

    if (ROLE::isRegistrar()){
      $allCC = (isset($countCC)?$countCC:0);
      $allAccepted = (isset($countAccepted)?$countAccepted:0);
      $noSubmission = $allCC - $allAccepted;
      $perAccepted = ($allCC == 0?0:($allAccepted / $allCC) * 100);
      $perNoSubmission = ($allCC==0?0:($noSubmission / $allCC) * 100);

    }

    function getEnrollee($arr, $status, $courseid, $fhe = ''){
        $out = 0;
        foreach($arr as $c){
          if ($c->Course == $courseid){
              if ($status == 2){
                if ($c->finalize == 2){
                  $out += $c->countEncoded;
                }

              }

              if ($status == 0 and $fhe == 'FHE'){
                if ($c->finalize == 0 and $c->TES == 0 and $c->SchoolLevel == "Under Graduate"){
                  $out += $c->countEncoded;
                }
              }

              if ($status == 0 and $fhe == 'ASSESS'){
                if ($c->finalize == 0 and ($c->TES == 1 or $c->TES == 2) and $c->SchoolLevel == "Under Graduate"){
                  $out += $c->countEncoded;
                }

                if ($c->finalize == 0 and ($c->SchoolLevel == "Masteral" or $c->SchoolLevel == "Doctoral")){
                  $out += $c->countEncoded;
                }

              }

              if ($status == 5){
                if ($c->finalize == 5){
                  $out += $c->countEncoded;
                }

              }

              if ($status == 1){
                if ($c->finalize == 1){
                  $out += $c->countEncoded;
                }

              }
          }
        }

        return $out;
    }
?>

@extends('layouts/contentNavbarLayout')

@section('title', config('variables.AppName'))

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection



@section('content')
<div class="row">
  <div class="col-lg-12 mb-4 order-0">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-sm-7">
          <div class="card-body">

            <h5 class="card-title text-primary">{{(auth()->check()?GENERAL::Greeting()." ".auth()->user()->emp->FirstName:"Guest")}}! 🎉</h5>
            <p class="mb-4">Welcome to the latest version of SLSU's Comprehensive Information System. Explore the newest features and updates by clicking the "What's New" button.</p>
            <!-- <p class="mb-4">Step into the cutting-edge realm of SLSU's revamped Comprehensive Information System! Hit the "What's New" button to unveil the hottest features and latest updates. Experience the seamless integration of all Information Systems into one dynamic platform.</p> -->
            <a href="{{route('whatsnew')}}" class="btn btn-sm btn-outline-primary">What's New</a>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="{{asset('assets/img/illustrations/man-with-laptop-light.png')}}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png">
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (empty(auth()->user()->HRMISID))
      <div class="card-body">
        <div class = "alert alert-warning">
          <strong>ENABLE GOOGLE SSO</strong><br>
          To access the platform seamlessly, please connect to HRMIS using Google Single Sign-On (SSO) for streamlined and secure authentication.
          <div class="mt-2">
            <a href = "{{route('my-account')}}" class = "btn btn-sm btn-warning">Visit page</a>
          </div>
        </div>
      </div>

  @endif


  @if(ROLE::isRegistrar() or  ROLE::isDepartment() or auth()->user()->AllowSuper == 1 or strtolower(auth()->user()->AccountLevel) == "administrator")
    <div class="col-lg-12 col-md-12 mb-4">
      <div class="card">
        <div class="row">
          <div class="col-sm-12">
            <div class="card-body">
              <h5 class="card-title text-primary">STUDENT MONITORING</h5>
              <p>for SY {{GENERAL::setSchoolYearLabel($SchoolYear, $Semester)}} - {{GENERAL::Semesters()[$Semester]['Long']}}</p>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr>
                    <th class = "text-nowrap">PROGRAM</th>
                    <th class = "text-nowrap text-center">FOR ENROLLMENT</th>
                    <th class = "text-nowrap text-center">FOR FHE</th>
                    <th class = "text-nowrap text-center">FOR ASSESSMENT</th>
                    <th class = "text-nowrap text-center">FOR VALIDATION</th>
                    <th class = "text-nowrap text-center">VALIDATED</th>
                    <th class = "text-nowrap text-center">TOTAL</th>
                  </tr>
                </thead>
                <tbody>

                    <?php
                      $forEncoding = 0;
                      $forFHE = 0;
                      $forAssess = 0;
                      $forValidation = 0;
                      $validated = 0;

                      $tforEncoding = 0;
                      $tforFHE = 0;
                      $tforAssess = 0;
                      $tforValidation = 0;
                      $tvalidated = 0;
                      $overallCourse = 0;
                      $overall = 0;
                    ?>
                  @foreach($courses as $course)
                    <?php
                        $forEncoding = getEnrollee($enrolmentstatuss, 2, $course->id);
                        $forFHE = getEnrollee($enrolmentstatuss, 0, $course->id, 'FHE');
                        $forAssess = getEnrollee($enrolmentstatuss, 0, $course->id, 'ASSESS');
                        $forValidation = getEnrollee($enrolmentstatuss, 5, $course->id);
                        $validated = getEnrollee($enrolmentstatuss, 1, $course->id);

                        $tforEncoding += $forEncoding;
                        $tforFHE += $forFHE;
                        $tforAssess += $forAssess;
                        $tforValidation += $forValidation;
                        $tvalidated += $validated;

                        $overallCourse = $forEncoding + $forFHE + $forAssess + $forValidation + $validated;
                        $overall += $overallCourse;
                    ?>
                    <tr>
                      <td class = "text-nowrap">{{$course->accro}}</td>
                      <td class = "text-nowrap text-center">{{$forEncoding}}</td>
                      <td class = "text-nowrap text-center">{{$forFHE}}</td>
                      <td class = "text-nowrap text-center">{{$forAssess}}</td>
                      <td class = "text-nowrap text-center">{{$forValidation}}</td>
                      <td class = "text-nowrap text-center">{{$validated}}</td>
                      <td class = "text-nowrap text-center">{{$overallCourse}}</td>
                    </tr>
                  @endforeach
                  <tr>
                      <td class = "text-nowrap fw-bold">TOTAL</td>
                      <td class = "text-nowrap text-center fw-bold">{{$tforEncoding}}</td>
                      <td class = "text-nowrap text-center fw-bold">{{$tforFHE}}</td>
                      <td class = "text-nowrap text-center fw-bold">{{$tforAssess}}</td>
                      <td class = "text-nowrap text-center fw-bold">{{$tforValidation}}</td>
                      <td class = "text-nowrap text-center fw-bold">{{$tvalidated}}</td>
                      <td class = "text-nowrap text-center">{{$overall}}</td>
                    </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif


  <!-- For Teacher -->
  @if(ROLE::isTeacher())
  <div class="col-sm-12 col-lg-4 order-2 order-md-3 order-lg-2 mb-4">
    <div class="card">
      <div class="row row-bordered g-0">

        <div class="col-md-12">
          <div class="card-body">
            <h5>GRADES</h5>
            <div class="text-center">
              <div class="dropdown">
                <form id = "frmDashboardGrowth">
                <select name = "SchoolYear" class = "btn btn-sm btn-outline-primary select-sy">
                  @foreach(GENERAL::SchoolYears() as $sy)
                      <option value="{{ $sy }}" <?=($sy==$SchoolYear?"Selected":"")?>>{{$sy}}</option>
                  @endforeach
                </select>

                <select name = "Semester" class = "btn btn-sm btn-outline-primary select-sem">
                  @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value = "{{$index}}" <?=($index==$Semester?"Selected":"")?>>{{$sem['Short']}}</option>
                  @endforeach
                </select>
                <a href="#" class="dashboardview btn btn-sm btn-outline-primary">View</a>
                </form>
              </div>
            </div>
          </div>
          <div id="growthChart" data = "{{$gGrowth}}"></div>

          <div class="text-center growthUnencoded text-danger fw-semibold pt-3 mb-2">{{100-$gGrowth}}% Unencoded</div>

          <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
            <div class="d-flex">
              <div class="me-2">
                <span class="badge bg-label-primary p-2"><i class='bx bx-message-square-edit text-primary' ></i></span>
              </div>
              <div class="d-flex flex-column">
                <small>Encoded</small>
                <h6 class="mb-0 no-encoded-students"><a class = "text-dark" href = "{{route('view.encoded',['SchoolYear' => $SchoolYear,'Semester'=>$Semester])}}">{{(isset($countGrade->cGrade)?$countGrade->cGrade:0)}} students</a></h6>
              </div>
            </div>
            <div class="d-flex">
              <div class="me-2">
                <span class="badge bg-label-danger p-2"><i class='bx bx-message-square-x text-danger' ></i></span>
              </div>
              <div class="d-flex flex-column">
                <small>Unencoded</small>
                <h6 class="mb-0 no-unencoded-students"><a class = "text-dark" href = "{{route('view.unencoded',['SchoolYear' => $SchoolYear,'Semester'=>$Semester])}}">{{(isset($countNoGrade->cGrade)?$countNoGrade->cGrade:0)}} students</a></h6>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- For Registrar -->
  @if(ROLE::isRegistrar())
    <div class="col-lg-8 col-md-12 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="card-title d-flex align-items-start justify-content-between">
            <!-- <div class="avatar flex-shrink-0"> -->
              ALL RECORDS

            <!-- </div> -->
            <div class="text-center">
              <div class="dropdown">
                <form id = "frmDashboardGradeSheet">
                <select name = "SchoolYear" class = "btn btn-sm btn-outline-primary select-sy">
                  @foreach(GENERAL::SchoolYears() as $sy)
                      <option value="{{ $sy }}" <?=($sy==$SchoolYear?"Selected":"")?>>{{$sy}}</option>
                  @endforeach
                </select>

                <select name = "Semester" class = "btn btn-sm btn-outline-primary select-sem">
                  @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value = "{{$index}}" <?=($index==$Semester?"Selected":"")?>>{{$sem['Short']}}</option>
                  @endforeach
                </select>
                <a href="#" class="dashboardgsview btn btn-sm btn-outline-primary">View</a>
                </form>
              </div>
            </div>
          </div>

          <div class="row">
              <div class="col-4">
                <span class="fw-semibold d-block mb-1">Total Schedules</span>
                <h3 class="card-title mb-2 gsTotalSc">{{number_format($allCC,0,'',',')}}</h3>
                <small class="text-success fw-semibold"></small>
              </div>

              <div class="col-4">
                <span class="fw-semibold d-block mb-1">Total Accepted</span>
                <h3 class="card-title mb-2 gsAccepted">{{number_format($allAccepted,0,'',',')}}</h3>
                <small class="text-success fw-semibold perAccepted"><i class='bx bx-up-arrow-alt'></i> {{number_format($perAccepted,2,'.','')}}%</small>
              </div>

              <div class="col-4">
                <span class="fw-semibold d-block mb-1">Total No Submission</span>
                <a target = "_blank" href = "{{route('list-no-submit-gradesheets',['sy' => $sy])}}"><h3 class="card-title mb-2 gsNoSubmission">{{number_format($noSubmission,0,'',',')}}</h3></a>
                <small class="text-danger fw-semibold perNoSubmission"><i class='bx bx-down-arrow-alt'></i> {{number_format($perNoSubmission,2,'.','')}}%</small>
              </div>
          </div>

          <p class = "m-0">Click on the number under "Total No Submission" to view the list.</p>
        </div>
      </div>
    </div>
  @endif

</div>

@endsection

@section('page-script')
<script src="{{asset('storage/js/dashboards-analytics.js?id=20240425b')}}"></script>
@endsection
