@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:void(0);">{{ $pageTitle }}</a>
            </li>
        </ol>
    </nav>

    {{-- table --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="card-title">
                        <strong style="color:#033874;">{{ $scholarshipName ?? 'Unknown Scholarship' }} </strong>
                        <strong style="color: #66a6ea;">Scholars</strong>
                    </h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- Search bar and filter button for students -->
                        <form method="GET" action="{{ route('add-scholar-view') }}" class="d-flex align-items-center">
                            <input type="hidden" name="id" value="{{ $encryptedScholarshipId }}">
                            <input type="hidden" name="name" value="{{ $scholarshipName }}">
                            <input type="search" name="query" id="searchScholar" class="form-control form-control-sm"
                                placeholder="Search Scholar..." style="width: 250px; min-width: 200px;"
                                value="{{ request('query') }}">
                            <select name="school_year" id="filterSchoolYear" class="form-select form-select-sm ms-2">
                                <option value="">Select School Year</option>
                                @foreach (GENERAL::SchoolYears() as $year)
                                    <option value="{{ $year }}"
                                        {{ request('school_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                            <select name="semester" id="filterSemester" class="form-select form-select-sm ms-2">
                                <option value="">Select Semester</option>
                                @foreach (GENERAL::Semesters() as $index => $sem)
                                    <option value="{{ $index }}"
                                        {{ request('semester') == $index ? 'selected' : '' }}>{{ $sem['Long'] }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-warning ms-2 me-4" id="filterButton">
                                <i class="fa fa-filter"></i>
                            </button>
                        </form>
                        {!! $headerAction !!}
                        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddScholar" aria-controls="offcanvasBackdrop"> <i
                                class="fa fa-plus"></i> Add Scholar</a>
                    </div>
                </div>

                <hr>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead style="background-color: #66a6ea; color: white;">
                                <tr>
                                    <th class="text-nowrap" style="color: white;">#</th>
                                    <th class="text-nowrap" style="color: white;">Student Name</th>
                                    <th class="text-nowrap" style="color: white;">Date Awarded</th>
                                    <th class="text-nowrap" style="color: white;">School Year</th>
                                    <th class="text-nowrap" style="color: white;">Semester</th>
                                    <th class="text-nowrap" style="color: white;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="scholarsTableBody">
                                @include('_partials.scholars-table', ['scholars' => $scholars])
                            </tbody>
                        </table>
                        <!-- Pagination on Bottom Right -->
                        <div class="d-flex justify-content-end mt-3">
                            <div class="pagination-sm">
                                {{ $scholars->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Scholar Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddScholar" aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                <i class="fa fa-plus"></i>
                Add scholars to
                <strong style="color: #033874;">{{ $scholarshipName ?? 'Unknown Scholarship' }}</strong>
                {{-- <strong style="color: #66a6ea;">Scholarship</strong> --}}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <hr>
        <form id="frmAddScholar">
            @csrf
            <div class="offcanvas-body">
                <div class="row">
                    <!-- School Year Select -->
                    <div class="col-md-6">
                        <label for="schoolYear" class="form-label">School Year</label>
                        <select class="form-select mb-3" id="schoolYear" name="schoolYear">
                            <option value="" disabled selected>School Year</option>
                            @foreach (GENERAL::SchoolYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Semester Select -->
                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-select mb-3" id="semester" name="semester">
                            <option value="" disabled selected>Semester</option>
                            @foreach (GENERAL::Semesters() as $index => $sem)
                                <option value="{{ $index }}">{{ $sem['Long'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label for="searchStudent" class="form-label">Search</label>
                <!-- Search Student Input -->
                <input type="search" id="searchStudent" class="form-control"
                    placeholder="Search by Student NO. or Student Name...">

                <!-- Results Dropdown -->
                <div id="studentResults" class="list-group mt-1 border rounded bg-white shadow-sm"></div>

                <!-- Selected Students List -->
                <div id="selectedStudents" class="mt-3" style="max-height: 500px; overflow-y: auto;">

                </div>

                <!-- Hidden Input for Scholarship ID -->
                <input hidden type="text" id="scholarship_id" name="scholarship_id"
                    value="{{ $encryptedScholarshipId }}">

                <!-- Submit Button -->
                <button type="submit" class="btn w-100 mt-3" style="background-color: #66a6ea; color: white;">Add
                    Scholars</button>
            </div>
        </form>
        <!-- Toast Container (Bottom Right) -->
        <div id="toastContainerCreate" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>
    </div>


    <!-- Edit Scholar Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditScholar"
        aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                <i class="fa fa-edit"></i>
                Edit
                <strong style="color: #033874;">{{ $scholarshipName ?? 'Unknown Scholarship' }}</strong>
                <strong style="color: #66a6ea;">Scholar</strong>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <hr>

        <form id="frmUpdateScholar">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <input hidden type="text" name="updateScholarID" id="id">

                <div class="container p-3 border rounded shadow-sm mb-3">
                    <h5 class="mb-3">Scholar Information</h5>

                    <div class="row mb-2">
                        <div class="col-md-5 d-flex align-items-center">
                            <label style="white-space: nowrap;" for="editStudentNo" class="form-label mb-0"><i
                                    class="fa fa-id-card me-2"></i>Student
                                No:</label>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <strong><span style="color: #66a6ea; white-space: nowrap;" id="editStudentNo"
                                    class="form-control-plaintext"></span></strong>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-5 d-flex align-items-center">
                            <label style="white-space: nowrap;" for="editStudentName" class="form-label mb-0"><i
                                    class="fa fa-user me-2"></i>Scholar
                                Name:</label>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <strong><span style="color: #66a6ea; white-space: nowrap;" id="editStudentName"
                                    class="form-control-plaintext"></span></strong>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-5 d-flex align-items-center">
                            <label style="white-space: nowrap;" for="editSchoolYear" class="form-label mb-0"><i
                                    class="fa fa-calendar me-2"></i>
                                School Year:</label>
                        </div>
                        <div class="col-md-6">
                            <strong><span style="color: #66a6ea; white-space: nowrap;" id="editSchoolYear"
                                    class="form-control-plaintext"></span></strong>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-5 d-flex align-items-center">
                            <label style="white-space: nowrap;" for="editSemester" class="form-label mb-0"><i
                                    class="fa fa-calendar me-2"></i>
                                Semester:</label>
                        </div>
                        <div class="col-md-6">
                            <strong><span style="color: #66a6ea; white-space: nowrap;" id="editSemester"
                                    class="form-control-plaintext"></span></strong>
                        </div>
                    </div>

                </div>

                <!-- Date Awarded (Date Picker) -->
                <div class="mb-3">
                    <label for="editDateAwarded" class="form-label">Date Awarded</label>
                    <input type="date" class="form-control" id="editDateAwarded" name="editDateAwarded">
                </div>

                <!-- Bank Account No -->
                <div class="mb-3">
                    <label for="editBankAccount" class="form-label">Bank Account No.</label>
                    <input type="text" class="form-control" id="editBankAccount" name="editBankAccount">
                </div>

                <!-- Submit Button -->
                <button type="button" id="btnUpdateScholar" class="btn w-100 mt-2 mb-3"
                    style="background-color: #66a6ea; color: white;">Update Scholar</button>
            </div>
        </form>
        <!-- Toast Container (Bottom Right) -->
        <div id="toastContainerUpdate" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholarshipnew.js')
    <script src="{{ asset('assets/js/ui-toasts.js') }}"></script>
@endsection
