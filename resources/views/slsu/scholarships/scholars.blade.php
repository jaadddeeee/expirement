@extends('layouts.contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    {{-- Nav-breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('scholarships.index') }}">Scholarships</a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:void(0);">{{ $pageTitle }}</a>
            </li>
        </ol>
    </nav>

    {{-- table --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm">
                <!-- Card Header -->
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h3 class="card-title mb-0">
                        <strong style="color:#033874;">{{ $scholarshipName ?? 'Unknown Scholarship' }} </strong>
                        <strong>Scholars</strong>
                    </h3>

                    <div class="d-flex align-items-center gap-2">
                        {!! $headerAction !!}
                        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddScholar" aria-controls="offcanvasBackdrop"> <i
                                class="fa fa-plus"></i>
                            Add</a>
                    </div>
                </div>

                <div class="card-body mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                        <!-- Search bar and filter button for students -->
                        <form class="row w-100 g-2" id="filterForm">
                            @csrf
                            <input type="hidden" id="scholarshipId" name="id" value="{{ $id }}">
                            <input type="hidden" name="name" value="{{ $scholarshipName }}">

                            <div class="col-12 col-md-auto">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="basic-addon-search31"><i
                                            class="bx bx-search"></i></span>
                                    <input type="search" name="searchScholar" id="searchScholar"
                                        class="form-control form-control-sm" placeholder="Search Scholar..."
                                        aria-label="Search Scholars..." aria-describedby="basic-addon-search31"
                                        style="width: 250px; min-width: 200px;">
                                </div>
                            </div>

                            <div class="col-12 col-md-auto">
                                <select style="height: 31px;" name="filterSchoolYear" id="filterSchoolYear"
                                    class="form-select form-select-sm">
                                    <option value="">Select School Year</option>
                                    @foreach (GENERAL::SchoolYears() as $year)
                                        <option value="{{ $year }}"
                                            {{ request('filterSchoolYear') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-12 col-md-auto">
                                <select style="height: 31px;" name="filterSemester" id="filterSemester"
                                    class="form-select form-select-sm">
                                    <option value="">Select Semester</option>
                                    @foreach (GENERAL::Semesters() as $index => $sem)
                                        <option value="{{ $index }}"
                                            {{ request('filterSemester') == $index ? 'selected' : '' }}>
                                            {{ $sem['Long'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-auto">
                                <button style="height: 31px;" type="submit" class="btn btn-sm btn-warning w-100"
                                    id="filterBtnScholars">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>

                        <!-- Copy Scholars Button -->
                        <div class="col-12 col-md-auto mt-3">
                            <button class="btn btn-sm btn-secondary w-100" id="copyScholarsButton" data-bs-toggle="modal"
                                data-bs-target="#copyScholarsModal" style="display: none;">
                                <i class="fa fa-copy me-2"></i> Copy <span id="copyCountBadge"
                                    class="badge bg-light text-dark ms-2"></span>
                            </button>
                        </div>

                        <!-- Delete Scholars Button -->
                        <div class="col-12 col-md-auto mt-3">
                            <button class="btn btn-sm btn-danger w-100" id="deleteScholarsBtn" style="display: none;">
                                <i class="fa fa-trash me-2"></i> Delete <span id="deleteCountBadge"
                                    class="badge bg-light text-dark ms-2"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <hr class="my-0">

                <!-- Card Body -->
                <div class="card-body pb-2">
                    <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Loading scholars...</p>
                    </div>

                    <div id="scholarsTable" class="table-responsive text-nowrap">
                        @include('slsu.scholarships._partials._scholars-table')
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
                Add new scholars to
                <strong style="color: #033874;">{{ $scholarshipName ?? 'Unknown Scholarship' }}</strong>
                {{-- <strong style="color: #66a6ea;">Scholarship</strong> --}}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <hr>
        <form id="frmAddScholar">
            @csrf
            <input type="hidden" id="scholarship_id" name="scholarship_id" value="{{ $id }}">
            <div class="offcanvas-body">
                <div id="addScholarMsg"></div>

                <!-- School Year Select -->
                <div>
                    <label for="schoolYear" class="form-label">School Year</label>
                    <select class="form-select mb-3" id="addSchoolYear" name="addSchoolYear">
                        <option value="" disabled selected>Select School Year</option>
                        @foreach (GENERAL::SchoolYears() as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Semester Select -->
                <div>
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-select mb-3" id="addSemester" name="addSemester">
                        <option value="" disabled selected>Select Semester</option>
                        @foreach (GENERAL::Semesters() as $index => $sem)
                            <option value="{{ $index }}">{{ $sem['Long'] }}</option>
                        @endforeach
                    </select>
                </div>

                <label for="searchStudent" class="form-label">Search Student to add in Scholarship</label>

                <!-- Search Student Input -->
                <input type="search" id="searchStudent" name="searchStudent" class="form-control"
                    placeholder="Search by Student NO. or Student Name...">

                <!-- Results Dropdown -->
                <div id="studentResults" class="list-group mt-1 border rounded bg-white shadow-sm"
                    style="max-height: 400px; overflow-y: auto;"></div>

                <!-- Selected Students List -->
                <div id="selectedStudents" class="mt-3" style="max-height: 450px; overflow-y: auto;"></div>

                <!-- Add Scholar Button -->
                <button id="addScholarBtn" type="button" class="btn w-100 mt-3 mb-5"
                    style="background-color: #66a6ea; color: white;">Add
                    Scholar</button>
            </div>
        </form>
    </div>

    <!-- Edit/Update Scholar Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditScholar"
        aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                <i class="fa fa-edit"></i>
                Edit
                <strong style="color: #033874;">{{ $scholarshipName ?? 'Unknown Scholarship' }}</strong>
                <strong>Scholar</strong>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <hr>

        <form id="frmUpdateScholar">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <div id="editScholarMsg"></div>

                <input type="hidden" name="id" id="id">

                <div class="container p-3 border rounded shadow-sm mb-3">
                    <h5 class="mb-3">Scholar Information</h5>

                    <div class="row mb-2">
                        <div class="col-md-5 d-flex align-items-center">
                            <label for="displayStudentNo" class="form-label mb-0"><i
                                    class="fa fa-id-card me-2 text-nowrap"></i>Student
                                No:</label>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <strong><span id="displayStudentNo"
                                    class="form-control-plaintext text-nowrap"></span></strong>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-5 d-flex align-items-center">
                            <label for="displayStudentName" class="form-label mb-0"><i
                                    class="fa fa-user me-2 text-nowrap"></i>Scholar
                                Name:</label>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <strong><span id="displayStudentName"
                                    class="form-control-plaintext text-nowrap"></span></strong>
                        </div>
                    </div>
                </div>

                <!-- Date Awarded -->
                <div class="mb-3">
                    <label for="editDateAwarded" class="form-label">Date Awarded</label>
                    <input type="date" class="form-control" id="editDateAwarded" name="editDateAwarded">
                </div>

                <!-- Bank Account No -->
                <div class="mb-3">
                    <label for="editBankAccount" class="form-label">Bank Account No.</label>
                    <input type="text" class="form-control" id="editBankAccount" name="editBankAccount">
                </div>

                <!-- Contact No -->
                <div class="mb-3">
                    <label for="editContactNo" class="form-label">Contact No.</label>
                    <input type="text" class="form-control" id="editContactNo" name="editContactNo">
                </div>

                <!-- Submit Button -->
                <button type="button" id="btnUpdateScholar" class="btn w-100 mt-2 mb-3"
                    style="background-color: #66a6ea; color: white;">Update Scholar</button>
            </div>
        </form>
    </div>

    <!-- Copy Scholars Modal -->
    <div class="modal fade" id="copyScholarsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Copy Scholars</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="copyScholarsForm">
                        @csrf
                        <input type="hidden" name="scholarship_id" value="{{ $id }}">
                        <input type="hidden" id="hiddenSchoolYearFrom" name="schoolYearFrom" value="">
                        <input type="hidden" id="hiddenSemesterFrom" name="semesterFrom" value="">

                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">From</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>School Year:</strong>
                                    <span id="displaySchoolYearFrom" class="text-danger">Not Set</span>
                                </p>
                                <p><strong>Semester:</strong>
                                    <span id="displaySemesterFrom" class="text-danger">Not Set</span>
                                </p>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">To</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="schoolYearTo" class="form-label">School Year</label>
                                        <select class="form-select" id="schoolYearTo" name="schoolYearTo" required>
                                            <option value="" disabled selected>Select School Year</option>
                                            @foreach (GENERAL::SchoolYears() as $year)
                                                <option value="{{ $year }}">{{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="semesterTo" class="form-label">Semester</label>
                                        <select class="form-select" id="semesterTo" name="semesterTo" required>
                                            <option value="" disabled selected>Select Semester</option>
                                            @foreach (GENERAL::Semesters() as $index => $sem)
                                                <option value="{{ $index }}">{{ $sem['Long'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="copyScholarsMsg" class="text-center mx-auto" style="max-width: 80%;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveCopyChanges">Copy Scholars</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholarships.js.scholars-js')
@endsection
