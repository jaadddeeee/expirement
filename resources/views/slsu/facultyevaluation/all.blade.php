
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">{{$pageTitle}}</a>
    </li>
  </ol>
</nav>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>

        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}

        </div>
      </div>
      <hr>
      <div class="card-body">
          <form method = "POST" action = "{{ route('pro-all-evaluation') }}">
            @csrf
            <div class="row g-3 align-items-center">
                @if(auth()->user()->AllowSuper == 1 or ROLE::isVPAA() or ROLE::isPresident())
                <div class="col-auto">
                  <label>Campus <span class = 'text-danger'>*</span></label>
                  <select name="Campus" id="Campus" class="form-select">
                    <option value="0">Select Campus</option>
                      @foreach(GENERAL::Campuses() as $index => $campus)
                        <option value="{{$index}}" <?=$index==$Campus?"Selected":""?>>{{$campus['Campus']}}</option>
                      @endforeach
                  </select>
                </div>
                @endif
                <div class="col-auto">
                  <label>Period <span class = 'text-danger'>*</span></label>
                  <select name="Period" id="Period" class="form-select">
                    <option value="0">Select Period</option>
                      @foreach($scheds as $sched)
                        <option value="{{$sched->id}}"  <?=$Period==$sched->id?"Selected":""?>>{{GENERAL::setSchoolYearLabel($sched->SchoolYear,$sched->Semester)}} {{GENERAL::Semesters()[$sched->Semester]['Long']}}</option>
                      @endforeach
                  </select>
                </div>

                <div class="col-auto">
                  <label>Status</label>
                  <select name="Status" id="Status" class="form-select">
                    <option value="0">Select Status</option>
                      @foreach($statuss as $status)
                        <option <?=($StatusStr==$status->EmploymentStatus?"Selected":"")?>>{{$status->EmploymentStatus}}</option>
                      @endforeach
                  </select>
                </div>
                @if(auth()->user()->AllowSuper == 1 or ROLE::isVPAA() or ROLE::isPresident())
                <div class="col-auto">
                  <label>Department</label>
                  <select name="Department" id="Department" class="form-select">
                    <option value="0">Select Department</option>
                    @foreach($deptlists as $deptlist)
                    <option value="{{$deptlist->id}}" <?=($DepartmentID==$deptlist->id?"Selected":"")?>>{{$deptlist->DepartmentName}}</option>
                    @endforeach
                  </select>
                </div>
                @endif
                <div class="col-auto">
                  <label>Faculty</label>
                  <i type="button" class="bx bx-info-circle text-nowrap waves-effect waves-light" data-bs-toggle="popover" data-bs-placement="left" data-bs-content="Keep status and department empty to search for all names that contain the entered string. Click me again to close" data-bs-original-title="Tips" aria-describedby="popover843637"></i>
                  <input type = 'text' value = "{{$FacultyName}}" name="Faculty" id="Faculty" class="form-control" placeholder = "Part of the name">
                </div>

                <div class="col-auto">
                  <label><br><br></label>
                  <span id="passwordHelpInline" class="form-text">
                    <button class = "btn btn-primary" type = "submit" id = "btnGenerateAllDepartment">Generate</button>
                  </span>
                </div>
            </div>
          </form>

          <div class="table-responsive mt-3">
            <table class="table table-sm table-striped">
              <thead>
                <tr>
                <tr>
                    <th class = "text-nowrap text-center">#</th>
                    <th>Faculty</th>
                    <th>Department</th>
                    <th class = "text-nowrap text-center">Commitment</th>
                    <th class = "text-nowrap text-center">Knowledge<br>of Subject</th>
                    <th class = "text-nowrap text-center">Teaching for<br>Independent Learning</th>
                    <th class = "text-nowrap text-center">Management<br>of Learning</th>
                  </tr>
                </tr>
              </thead>
              <tbody>
                @foreach($perSchedAVGs as $sched)

                  @if (!empty($sched->C1))
                    <tr>
                      <td class = "text-nowrap text-center">{{isset($ctr)?++$ctr:$ctr=1}}</td>
                      <td class = "text-nowrap">
                        <strong class = 'text-primary'>{{strtoupper($sched->LastName.', '.$sched->FirstName)}}</strong>
                        <br>
                        <em>{{$sched->EmploymentStatus}}</em>
                      </td>
                      <td class = "text-nowrap">{!!wordwrap($sched->DepartmentName, 20, "<br>")!!}</td>
                      <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched->C1,2,'.','')}}</strong><br>({{number_format($sched->P1,2,'.','')}}) {!!GENERAL::AdjectiveRating($sched->P1)!!}</td>
                      <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched->C2,2,'.','')}}</strong><br>({{number_format($sched->P2,2,'.','')}}) {!!GENERAL::AdjectiveRating($sched->P2)!!}</td>
                      <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched->C3,2,'.','')}}</strong><br>({{number_format($sched->P3,2,'.','')}}) {!!GENERAL::AdjectiveRating($sched->P3)!!}</td>
                      <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched->C4,2,'.','')}}</strong><br>({{number_format($sched->P4,2,'.','')}}) {!!GENERAL::AdjectiveRating($sched->P4)!!}</td>
                    </tr>

                    <tr>
                      <td colspan = "7" class = "text-left">FEEDBACKS:<br>
                      <ul>
                      @foreach($feeds as $fb)
                        @if ($fb->EmployeeID == $sched->EmployeeID)
                        <li>{!!wordwrap($fb->Feedback, 150, "<br>")!!}</li>
                        @endif
                      @endforeach
                      </ul>
                      </td>
                    </tr>

                  @endif
                @endforeach
              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="{{asset('assets/js/ui-popover.js')}}"></script>
@include('all.js')
@endsection
