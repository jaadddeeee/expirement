
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
    <div class="card m-0">
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
          <form id = "frmGenerate">
              <div id = "certmsg"></div>
              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="Type" class="form-label">Type: <span class = "text-danger">*</span></label>
                      <select class = "form-select" name = "Type" id = "Type">
                          <option value="0"></option>
                          <option value="1">Enrollment</option>
                          <!-- <option value="2">Employment (Board)</option> -->
                          <option value="5">Endorsement (CAV)</option>
                          <option value="6">Certification (Graduated)</option>
                          <option value="7">Certificate of Transfer Credential (CTC)</option>
                          <!-- ang 3 naas osas -->
                      </select>
                  </div>
              </div>

              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                    <label for="allsearch" class="form-label">Search Name / Student: <span class = "text-danger">*</span></label>
                    <input type="text" id="allsearch" name = "allsearch" class="typeahead form-control" placeholder="Enter Name / Student" autofocus>
                  </div>
              </div>

              <div class="row all latinhonor">
                  <div class="col-lg-4 col-sm-12 col-xs-12 mb-3">
                      <label for="LatinHonor" class="form-label">Latin Honor (if any)</label>
                      <input type = "text" class = "form-control" name = "LatinHonor" id = "LatinHonor">
                  </div>
              </div>

              <div class="row all ctc">
                  <div class="col-lg-4 col-sm-12 col-xs-12 mb-3">
                      <label for="NameofSchool" class="form-label">Name of School</label>
                      <input type = "text" class = "form-control" name = "NameofSchool" id = "NameofSchool">
                  </div>
              </div>

              <div class="row all ctc">
                  <div class="col-lg-4 col-sm-12 col-xs-12 mb-3">
                      <label for="TransferAddress" class="form-label">Address</label>
                      <input type = "text" class = "form-control" name = "TransferAddress" id = "TransferAddress">
                  </div>
              </div>

              <div class="row all latinhonor">
                  <div class="col-lg-4 col-sm-12 col-xs-12 mb-3">
                      <label for="LatinHonor" class="form-label">Latin Honor (if any)</label>
                      <input type = "text" class = "form-control" name = "LatinHonor" id = "LatinHonor">
                  </div>
              </div>

              <div class="row all">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="SchoolYear" class="form-label">School Year:</label>
                      <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                          <option value="0"></option>
                          @foreach(GENERAL::SchoolYears() as $index => $sy)
                            <option value="{{$sy}}">{{$sy}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>

              <div class="row all">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="Semester" class="form-label">Semester:</label>
                      <select class = "form-select" name = "Semester" id = "Semester">
                          <option value="0"></option>
                          @foreach(GENERAL::Semesters() as $index => $sem)
                            <option value="{{$index}}">{{$sem['Long']}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>

              <div class="row all purpose">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="Purpose" class="form-label">Purpose:</label>
                      <select class = "form-select" name = "Purpose" id = "Purpose">
                          <option value="0"></option>
                          @foreach($purposes as $pur)
                            <option>{{$pur->Description}}</option>
                          @endforeach
                      </select>

                  </div>
              </div>

              <div class="row all orno">

                <div class=" col-lg-2 col-sm-6 col-xs-12 mb-3">
                    <label for="ORNo" class="form-label">OR No</label>
                    <div class="input-group flex-nowrap">
                      <span class="input-group-text" id="addon-orno1" data-bs-toggle="offcanvas" data-bs-target="#offcanvassearchOR"><i class='bx bx-search-alt-2'></i></span>
                      <input readonly name = "ORNo" id = "ORNo" type="number" class="form-control" placeholder="ORNo" aria-label="ORNo" aria-describedby="addon-wrapping">
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6 col-xs-12 mb-3">
                    <label for="ORDate" class="form-label">Date Paid</label>
                    <input readonly type = "date" class = "form-control" name = "ORDate" id = "ORDate">
                </div>
              </div>

              <div class="row all docorno">

                  <div class=" col-lg-2 col-sm-6 col-xs-12 mb-3">
                      <label for="DocORNo" class="form-label">Doc Stamp OR No</label>
                      <div class="input-group flex-nowrap">
                        <span class="input-group-text" id="addon-orno2" data-bs-toggle="offcanvas" data-bs-target="#offcanvassearchOR"><i class='bx bx-search-alt-2'></i></span>
                        <input readonly name = "DocORNo" id = "DocORNo" type="number" class="form-control" placeholder="DocORNo" aria-label="DocORNo" aria-describedby="addon-wrapping">
                      </div>
                  </div>

                  <div class="col-lg-2 col-sm-6 col-xs-12 mb-3">
                      <label for="DocORDate" class="form-label">Doc Stamp Date Paid</label>
                      <input readonly type = "date" class = "form-control" name = "DocORDate" id = "DocORDate">
                  </div>

              </div>

              <div class = "row">
                  <div class="col-lg-2 col-sm-6 col-xs-12 mb-3">
                      <button id="btnGenerate" class="btn btn-primary">Generate Certificate</button>
                  </div>
              </div>
          </form>
      </div>

    </div>
  </div>
</div>

@include('slsu.certificates.offcanvass')
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/registrar.js?id=20241114a')}}"></script>
@include('slsu.certificates.or')
<script>
  $(".all").hide();
</script>
<script src="{{asset('storage/js/typeahead.js?id=20231205b')}}"></script>
@endsection
