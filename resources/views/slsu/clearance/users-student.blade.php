
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>

        </div>
        <div class="card-action">
            <a href = "#" class = "btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvassAddClearance" aria-controls="offcanvasScroll"><i class='bx bxs-user-plus'></i> New User</a>

        </div>
      </div>
      <hr>
      <div class="card-body">
          <div id = "bulksms"></div>
          <div class="table-responsive">
              <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th class = 'text-center'>Action</th>
                      <th>StudentNo</th>
                      <th>Name</th>
                      <th>Course</th>
                      <th>Department</th>
                      <th>Account Type</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($students as $student)
                    <tr id = "sid-{{$student->id}}">
                      <td>{{isset($ctr)?++$ctr:$ctr=1}}</td>
                      <td class = 'text-center'><a href = "#" sid = "{{$student->id}}" class = 'deleteclearance' cid = '{{Crypt::encryptstring($student->id)}}'><i class='text-danger bx bx-trash'></i></a></td>
                      <td>{{$student->StudentNo}}</td>
                      <td>
                      {{utf8_decode($student->LastName.', '.$student->FirstName)}}
                      </td>
                      <td>{{$student->accro}}</td>
                      <td>{{$student->DepartmentName}}</td>
                      <td>{{$student->ClearanceFlag}}</td>

                    </tr>
                    @endforeach
                  </tbody>
              </table>
          </div>
      </body>

    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvassAddClearance" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class='bx bxs-user-plus'></i> New User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmNewClearance">
      @csrf
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">


        <div class="input-group mb-3">

          <input type="text" name = "studentinfo" class="form-control studentname typeahead" placeholder = "Enter Student Number" id = "allsearch">
          <span class="input-group-text"><i class = "fa fa-search"></i></span>
        </div>

        <div class="form-group mb-3">
          <label>Select Account Type</label>
          <select class = "form-select" name = 'Accountype' id = 'Accountype'>
              <option></option>
              <option>Department</option>
              <option>SSC</option>
          </select>
        </div>
        <button type="submit" id = "btnsavenewclearanceuser" class="btn btn-primary mb-2  w-100"><i class='bx bxs-user-plus'></i> Save</button>
        <button type="button" class="btn btn-outline-secondary  w-100"  data-bs-dismiss="offcanvas">Cancel</button>

        <div class = "divider">
          <span class = "divider-text">Result</span>
        </div>

        <div id = "clearanceres"></div>

      </div>
    </form>
</div>


@endsection


@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/typeahead.js?id=20230823')}}"></script>
@include('slsu.clearance.js')
@endsection
