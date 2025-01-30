
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
            <a href="#" class="btn btn-sm btn-danger" data-bs-target = "#modalManualEnrol" data-bs-toggle = 'modal'>By-pass Step 1</a>
            <a href="#" class="btn btn-sm btn-warning" data-bs-target = "#modalManualSearchStudent" data-bs-toggle = 'modal'>Add/Change/Drop Schedule</a>
        </div>
      </div>
      <hr>
      <div class="card-body">
            <div class="table-responsive">
                Note: Text enclosed with <div title="need finalize to continue" class="badge bg-danger">red</div> indicates that a subject or schedule has been added but may have been forgotten to finalize.
                <div class="row g-3 align-items-center mt-2">
                  <div class="col-auto">
                    <input id = "filterStudentNo" type = "text" class = "form-control" placeholder = "Filter by Student No">
                  </div>
                </div>
                <div id="ressearchstep2" class = "mt-2">
                @include('slsu.department.step2res')
                </div>

            </div>
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
                    <option value = "{{$status->status}}">{{$status->status}}</option>
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
                    <option>{{$x}}</option>
                  @endfor
              </select>
            </div>
            <div class="col mb-0">
              <label for="StudentSection" class="form-label">Section</label>
              <input type="text" id="StudentSection" name = "StudentSection" class="form-control">
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

<div class="modal fade" id="modalManualEnrol" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="backDropModalTitle">Manual Enroll</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <hr class = "m-0">
      <div class="modal-body">
        <form id = "frmModalManualStudentEnroll">
          <div id="manualmessage"></div>
          <div class="row g-2 mb-3">
            <div class="col mb-0">
              <label for="manualStudentNo" class="form-label">Student No</label>
              <input type = "text" class="form-control" id = "manualStudentNo" name = "manualStudentNo" placeholder="StudentNo">
            </div>
          </div>

          <div class="row">
            <div class="col mb-3">
              <label for="manualStudentStatus" class="form-label">Select Status</label>
              <select class="form-select" id = "manualStudentStatus" name = "manualStudentStatus" placeholder="Select Status">
                  <option value = ""></option>
                  @foreach($statuss as $status)
                    <option value = "{{$status->status}}">{{$status->status}}</option>
                  @endforeach
              </select>
            </div>
          </div>

          <div class="row g-2">
            <div class="col mb-0">
              <label for="manualStudentYear" class="form-label">Student Year</label>
              <select class="form-select" id = "manualStudentYear" name = "manualStudentYear" placeholder="Select Year">
                  <option value = ""></option>
                  @for($x=1;$x<=12;$x++)
                    <option>{{$x}}</option>
                  @endfor
              </select>
            </div>
            <div class="col mb-0">
              <label for="manualStudentSection" class="form-label">Section</label>
              <input type="text" id="manualStudentSection" name = "manualStudentSection" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id = "btnManualEnrolmentProceed" class="btn btn-primary">Proceed</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalManualSearchStudent" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Search Enrollee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id = "frmManualSearchStudent">
            @csrf
            <div id = "manualmsg"></div>
            <div class="row">
              <div class="col mb-3">
                <label for="str" class="form-label">Search Name / Student:</label>
                <input type="text" id="str" name = "str" class="typeahead form-control" placeholder="Enter Name / Student" autofocus>
              </div>
            </div>

            <div class="row g-2">
              <div class="col mb-0">
                <label for="Description" class="form-label">School Year:</label>
                <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                    <option value="0"></option>
                    @foreach(GENERAL::SchoolYears() as $index => $sy)
                      <option value="{{$sy}}">{{$sy}}</option>
                    @endforeach
                </select>
              </div>

              <div class="col mb-0">
                <label for="Semester" class="form-label">Semester:</label>
                <select class = "form-select" name = "Semester" id = "Semester">
                    <option value="0"></option>
                    @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value="{{$index}}">{{$sem['Long']}}</option>
                    @endforeach
                </select>
              </div>

            </div>
          </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id = "btnManualSearchStudent" class="btn btn-primary"><i class = "mdi mdi-account-search-outline"></i> Search</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="{{asset('storage/js/department.js?id=20241226')}}"></script>
@include('slsu.department.js')
@endsection
