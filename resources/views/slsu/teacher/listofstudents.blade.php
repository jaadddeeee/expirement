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
            {!! $headerAction ?? '' !!}
        </div>
      </div>
      <div class="card-body">
          @if (count($lists) <= 0)
              {!! GENERAL::Error("No student enrolled") !!}
          @else
            <div class = "row">
              <div class = 'col-xs-12 col-lg-7'>
                <div class = "table-responsive">

                    <table class = 'table table-hover table-sm'>
                        <thead>
                            <tr>
                              <td>#</td>
                              <td>StudentNo</td>
                              <td>Name</td>
                              <td>Course</td>
                              <td>Year/Section</td>
                              <td class = 'text-nowrap'>Description</td>
                            </tr>
                        </thead>
                        <tbody>
                    @php
                        $male = 0;
                        $female = 0;
                        $validated = 0;
                    @endphp
                    @foreach($lists as $enrolled)
                        @php
                            if ($enrolled->Sex == "M")
                              $male++;
                            else
                              $female++;
                        @endphp

                        @if ($enrolled->finalize == 1)
                        @php
                        $validated++;
                        @endphp
                            <tr>
                              <td class = 'text-nowrap'>{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                              <td class = 'text-nowrap'><a class = "showonestudent" href = "#" aname = "{{ucwords(strtolower(utf8_decode($enrolled->LastName . ', '. $enrolled->FirstName)))}}" sid = "{{Crypt::encryptstring($enrolled->StudentNo)}}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasperStudent" aria-controls="offcanvasBackdrop">{{$enrolled->StudentNo}}</a></td>
                              <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($enrolled->LastName . ', '. $enrolled->FirstName)))}}</td>
                              <td class = 'text-nowrap'>{{$enrolled->accro}}</td>
                              <td class = 'text-nowrap'>{{$enrolled->StudentYear . (empty($enrolled->Section)?"":"-".strtoupper($enrolled->Section))}}</td>
                              <td class = 'text-nowrap'>{{$enrolled->coursetitle}}</td>
                            </tr>
                        @endif

                    @endforeach
                        </tbody>
                    </table>


                </div>
              </div>
              <div class = 'col-xs-12 col-lg-5'>
                  <div class = "mt-1 ml-5 mt-2">
                      <h5>SUMMARY</h5>
                      <div>Total registered: <strong>{{count($lists)}}</strong></div>
                      <div>Total validated: <strong>{{$validated}}</strong></div>
                      <div>Male: <strong>{{$male}}</strong></div>
                      <div>Female: <strong>{{$female}}</strong></div>
                      <div>Course No: <strong>{{$subinfo->courseno}}</strong></div>
                      <div>Description: <strong>{{$subinfo->coursetitle}}</strong></div>
                      <div>Course Code: <strong>{{$lists[0]->coursecode}}</strong></div>
                      <div>Schedule: <strong>{{$lists[0]->Time1 . (!empty($lists[0]->Time2)?" and ".$lists[0]->Time2:"")}}</strong></div>
                      <button class = "mt-1 btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBackdrop" aria-controls="offcanvasBackdrop">Send SMS</button>
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
                            <button type="button" id = "sendbulksms" class="btn btn-primary mb-2  w-100">Send Now</button>
                            <button type="button" class="btn btn-outline-secondary  w-100" data-bs-dismiss="offcanvas">Cancel</button>
                          </div>
                        </form>
                      </div>
                      <a href = "{{ route('classrecord', ['id' => Crypt::encryptstring($ID),'code' => Str::slug($lists[0]->coursecode), 'courseno' => Str::slug($subinfo->courseno)]) }}" class = "mt-1 btn btn-sm btn-primary">Generate Class Record</a>

                  </div>

              </div>
            </div>
          @endif
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasperStudent" aria-labelledby="offcanvasBackdropLabelx">
  <div class="offcanvas-header">
    <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-user"></i> Student Information</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body my-auto mx-0 flex-grow-0">
    <div id = "off-one-student"></div>
  </div>

</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="e" aria-labelledby="offcanvasBackdropLabel">
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
        <button type="button" id = "btnsendonesms" class="btn btn-primary mb-2  w-100">Send Now</button>
        <button type="button" class="btn btn-outline-secondary  w-100" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
</div>
@endsection

@section('page-script')
<script src="{{asset('storage/js/teacher.js?id=20230819a')}}"></script>
@endsection
