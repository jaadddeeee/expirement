
@extends('layouts/contentNavbarLayout')

@section('title', 'Manage Clearance')


@section('content')

<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Clearance /</span> List
</h4>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>
          <span class = "text-primary">{{$Role}}  </span>
        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}
            <a href = "#" class = "btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCLINIC">New</a>
            <a href = "#" class = "btn btn-sm btn-warning" data-bs-toggle="offcanvas" data-bs-target="#offcanvasImportFile" aria-controls="offcanvasBackdrop">Import from file...</a>
        </div>
      </div>
      <hr>
      <div class="card-body">
          <div id = "bulksms"></div>
          <div class = "table-responsive">
              <div class="row">
                <div class="col-lg-6 col-sm-12">
                    {{$depts->links()}}
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="input-group mb-3">
                      <span class="input-group-text">Search</span>
                      <input type="text" class="form-control" placeholder = "Enter Student Number / Name" id = "clearancesearch">
                      <span class="input-group-text"><i class = "fa fa-search"></i></span>
                    </div>
                </div>
              </div>
              <div id = "ajaxcall">
                <table class = "table table-sm">
                    <tr>
                        <td>Action</td>
                        <td>Student Name</td>
                        <td>Course / Major</td>
                        <td>SY/Sem</td>
                        <td>Reason</td>
                    </tr>

                    @foreach($depts as $dept)

                        <tr>
                            <td>
                              <a href = "#" class = "removeclearance" sid = "{{Crypt::encryptstring($dept->id)}}">
                                <i class = "fa fa-trash text-danger"></i>
                              </a>


                            </td>
                            <td class = "text-nowrap">{!!"<strong>".$dept->StudentNo. '</strong> ' . utf8_decode($dept->student->LastName.', '.$dept->student->FirstName . ' '.(empty($dept->student->MiddleName)?"":$dept->student->MiddleName[0]))!!}</td>
                            <td class = "text-nowrap">{{utf8_decode($dept->student->course->accro.(isset($dept->student->Major->course_major)?" - ".$dept->student->Major->course_major:""))}}</td>
                            <td class = "text-nowrap">{{\GENERAL::setSchoolYearLabel($dept->SchoolYear,$dept->Semester) . " - " . \GENERAL::Semesters()[$dept->Semester]['Long']}}</td>
                            <td class = "text-nowrap">{{$dept->Description}}</td>
                        </tr>
                    @endforeach
                </table>
              </div>
              <br>
              {{$depts->links()}}
          </div>
        </div>

    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasImportFile" aria-labelledby="offcanvasBackdropLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-upload"></i> Upload bulk entries</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <form id = "frmImportBulk" enctype="multipart/form-data">
    <div class="offcanvas-body my-auto mx-0 flex-grow-0">


      <label for="fileexcel" class="form-label">Select the excel file to import <span class = "text-danger">*</span></label>
      <input class="form-control mb-2" name = "fileexcel" id="fileexcel" type="file" accept = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
      <div class = "alert alert-info">
        Excel Column<br>
        A => Student Number<br>
        B => School Year (in 4 digit) <br>
        C => Semester (1,2, 9 for Summer)<br>
        D => Reason<br>
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

@include('slsu.modals.clinic');

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/clinic.js?id=20231225')}}"></script>
<script src="{{asset('storage/js/typeahead.js?id=20230823')}}"></script>
@endsection
