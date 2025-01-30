@if (count($reqs) <= 0)
  <div class = 'alert alert-danger'><strong>{{$CourseCode}}</strong> not found or no enrolled student.</div>
@else
  @if($reqs[0]->RequireReqForm != 1)
    <div class = 'alert alert-danger'><strong>{{$CourseCode}}</strong> is not a requested subject.</div>
  @else
    <div class = "table-responsive">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
            <div class="button-wrapper">
                <a href="{{route('report-qfin65', ['id' => Crypt::encryptstring($CourseCode),'sy' => Crypt::encryptstring($SchoolYear), 'sem' => Crypt::encryptstring($Semester), 'cashier' => Crypt::encryptstring(0)])}}" class="text-end btn btn-success">Generate QF-IN65 only</a>
                @if($feeperstudent <> "waive")
                <a href="{{route('report-qfin65', ['id' => Crypt::encryptstring($CourseCode),'sy' => Crypt::encryptstring($SchoolYear), 'sem' => Crypt::encryptstring($Semester), 'cashier' => Crypt::encryptstring(1)])}}" class="text-end btn btn-danger">Generate QF-IN65 and reflect in cashiering</a>
                <a href="{{route('qfin67', ['id' => Crypt::encryptstring($CourseCode),'sy' => Crypt::encryptstring($SchoolYear), 'sem' => Crypt::encryptstring($Semester)])}}" class="text-end btn btn-primary">Generate QF-IN67</a>
                @endif
                <div class = "mt-3" style = "font-size: 20px; font-weight: 600">{{$ccs->courseno}} | {{$ccs->coursetitle}}</div>
                <small>{{strtoupper($ccs->FirstName." ".$ccs->LastName)}} | {{$ccs->EmploymentStatus}}</small><br>
                <small>{{$ccs->accro." | ".(empty($ccs->Time1)?"Schedule not set":$ccs->Time1).(empty($ccs->Time2)?"":" and ".$ccs->Time2)}}<br>
            </div>
        </div>
        <div class="card-action">
          <a href="#" id = "btnTransferCode" sid = "{{$CourseCode}}" class="text-end btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneAddStudent" aria-controls="offcanvasBackdrop">Add Student</a>
        </div>
      </div>

      <table class = "table table-sm table-hover">

          <thead>
              <tr>
                <th class = "text-nowrap text-center">Action</th>
                <th class = "text-nowrap">#</th>
                <th class = "text-nowrap">ID</th>
                <th class = "text-nowrap">StudentNo</th>
                <th class = "text-nowrap">Name</th>
                <th class = "text-nowrap text-end">Fee</th>
                <th class = "text-nowrap">Course</th>
                <th class = "text-nowrap">CourseNo</th>
                <th class = "text-nowrap">Title</th>
              </tr>
          </thead>
          <tbody>
          @foreach($reqs as $req)
              <tr>
                <td class = "text-center"><a href = "#" sy = {{Crypt::encryptstring($SchoolYear)}} sem = "{{Crypt::encryptstring($Semester)}}" gid = "{{Crypt::encryptstring($req->GID)}}" class = "delsub"><i class = "fa fa-trash text-danger fa-lg"></i></a></td>
                <td>{{(isset($ctr)?++$ctr:$ctr=1)}}.</td>
                <td class = "text-nowrap">{{$req->GID}}</td>
                <td class = "text-nowrap">{{$req->StudentNo}}</td>
                <td class = "text-nowrap">{{utf8_decode($req->LastName.', '.$req->FirstName)}}</td>
                <td class = "text-nowrap text-end text-danger fw-bold">{!!($feeperstudent == "waive"? "waived": number_format($feeperstudent,2))!!}</td>
                <td class = "text-nowrap">{{$req->accro}}</td>
                <td class = "text-nowrap">{{$req->courseno}}</td>
                <td class = "text-nowrap">{{$req->coursetitle}}</td>
              </tr>
          @endforeach
          </tbody>
      </table>
    </div>
  @endif
@endif

