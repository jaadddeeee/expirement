@extends('layouts.contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    <!-- Tagify CSS -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css"> --}}

    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- Jodit Text Editor -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jodit/4.2.47/es2021/jodit.min.css" />

    {{-- Nav-breadcrumb --}}
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

    {{-- Card/Table Section --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm">
                <!-- Card Header -->
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h3 class="card-title mb-0 text-secondary">
                        <strong>{{ $pageTitle ?? 'List' }}</strong>
                    </h3>

                    <div class="d-flex align-items-center gap-2">
                        {!! $headerAction ?? '' !!}

                        {{-- <a href="#" class="btn btn-sm btn-success" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddScholarship" aria-controls="offcanvasBackdrop">
                            <i class="fa fa-plus"></i> New
                        </a> --}}

                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                            data-bs-target="#createScholarshipModal">
                            <i class="fa fa-plus"></i> New
                        </button>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                        <!-- Search and Filters -->
                        <form class="row w-100 g-2" id="filterForm">
                            @csrf
                            <div class="col-12 col-md-auto">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="basic-addon-search31"><i
                                            class="bx bx-search"></i></span>
                                    <input type="search" id="searchScholarship" name='searchScholarship'
                                        class="form-control form-control-sm" placeholder="Search Scholarships..."
                                        aria-label="Search Scholarships..." aria-describedby="basic-addon-search31"
                                        style="width: 250px; min-width: 200px;" />
                                </div>
                            </div>

                            <div class="col-12 col-md-auto">
                                <select style="cursor: pointer; height: 31px;" class="form-select form-select-sm"
                                    id="filterScholarshipType" name="scholarshipType">
                                    <option value="">Scholarship Types</option>
                                    @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-auto">
                                <select style="cursor: pointer; height: 31px;" class="form-select form-select-sm"
                                    id="filterExternalType" name="externalType">
                                    <option value="">External Types</option>
                                    @foreach (GENERAL::ExternalSchType() as $index => $sch)
                                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-auto">
                                <button style="height: 31px;" type="submit" class="btn btn-sm btn-warning w-100"
                                    id="filterBtnScholarships">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
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
                        <p class="mt-2 mb-0">Loading scholarships...</p>
                    </div>

                    <div id="scholarshipTable" class="table-responsive text-nowrap">
                        @include('slsu.scholarships._partials._scholarships-table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Scholarship Modal -->
    <div class="modal fade" id="createScholarshipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">
                        <i class="fa fa-plus me-2"></i> New Scholarship
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="frmAddScholarship">
                        @csrf
                        <!-- Scholarship Type -->
                        <div class="mb-3">
                            <label for="scholarshipType" class="form-label">Scholarship Type</label>
                            <select style="cursor: pointer;" class="form-select" name="ScholarshipType"
                                id="scholarshipType">
                                <option value="" disabled selected>Select Scholarship Type</option>
                                @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <!-- Scholarship Name -->
                            <div class="col mb-0">
                                <label for="scholarshipName" class="form-label">Scholarship Name</label>
                                <input id="scholarshipName" type="text" name="ScholarshipName" class="form-control"
                                    placeholder="Enter Scholarship Name">
                            </div>

                            <!-- Scholarship Acronym -->
                            <div class="col mb-0">
                                <label for="schAcronym" class="form-label">Scholarship Acronym</label>
                                <input id="schAcronym" type="text" name="SchAcronym" class="form-control"
                                    placeholder="Enter Acronym">
                            </div>
                        </div>

                        <!-- External Scholarship Type -->
                        <div id="externalOptions" class="mb-3" style="display: none;">
                            <label for="externalScholarshipType" class="form-label">External Type</label>
                            <select style="cursor: pointer;" class="form-select" name="ExternalScholarshipType"
                                id="externalScholarshipType">
                                <option value="" disabled selected>Select External Type</option>
                                @foreach (GENERAL::ExternalSchType() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scholarship Provider -->
                        <div class="mb-3" style="display: none;">
                            <label for="schProvider" class="form-label">Scholarship Provider</label>
                            <input id="schProvider" type="text" class="form-control"
                                placeholder="Enter Scholarship Provider" disabled>
                            <input id="schProviderHidden" type="hidden" name="SchProvider">
                        </div>
                    </form>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnStoreScholarship">
                        <i class="fa fa-save me-1"></i> Create Scholarship
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Scholarship Modal -->
    <div class="modal fade" id="editScholarshipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">
                        <i class="fa fa-edit me-2"></i> Edit Scholarship
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="frmEditScholarship">
                        @csrf
                        <input type="hidden" id="editScholarshipId" name="id">

                        <!-- Scholarship Type -->
                        <div class="mb-3">
                            <label for="editScholarshipType" class="form-label">Scholarship Type</label>
                            <select style="cursor: pointer;" class="form-select" name="ScholarshipType"
                                id="editScholarshipType">
                                <option value="" disabled>Select Scholarship Type</option>
                                @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <!-- Scholarship Name -->
                            <div class="col mb-0">
                                <label for="editScholarshipName" class="form-label">Scholarship Name</label>
                                <input id="editScholarshipName" type="text" name="ScholarshipName"
                                    class="form-control" placeholder="Enter Scholarship Name">
                            </div>

                            <!-- Scholarship Acronym -->
                            <div class="col mb-0">
                                <label for="editSchAcronym" class="form-label">Scholarship Acronym</label>
                                <input id="editSchAcronym" type="text" name="SchAcronym" class="form-control"
                                    placeholder="Enter Acronym">
                            </div>
                        </div>

                        <!-- External Scholarship Type -->
                        <div id="editExternalOptions" class="mb-3" style="display: none;">
                            <label for="editExternalScholarshipType" class="form-label">External Type</label>
                            <select style="cursor: pointer;" class="form-select" name="ExternalScholarshipType"
                                id="editExternalScholarshipType">
                                <option value="" disabled>Select External Type</option>
                                @foreach (GENERAL::ExternalSchType() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scholarship Provider -->
                        <div class="mb-3">
                            <label for="editSchProvider" class="form-label">Scholarship Provider</label>
                            <input id="editSchProvider" type="text" class="form-control"
                                placeholder="Enter Scholarship Provider" disabled>
                            <input id="editSchProviderHidden" type="hidden" name="SchProvider">
                        </div>
                    </form>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnUpdateScholarship">
                        <i class="fa fa-save me-1"></i> Update Scholarship
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Requirements Modal -->
    <div class="modal fade" id="addRequirementsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="addRequirementsModalLabel">Add Requirements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="addRequirementsForm">
                        @csrf
                        <input type="hidden" id="addRequirements" name="id">

                        <div id="requirementsContainer" class="border rounded p-3"
                            style="max-height: 500px; overflow-y: auto;">
                            <!-- Requirements will be dynamically added here -->
                        </div>

                        <button type="button" class="btn btn-sm btn-success mt-2" id="btnAddRequirement">
                            <i class="fa fa-plus"></i> Add Requirement
                        </button>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveRequirements">
                        <i class="fa fa-save me-1"></i> Save Requirements
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Requirements Modal -->
    <div class="modal fade" id="editRequirementsModal" tabindex="-1" aria-labelledby="editRequirementsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRequirementsModalLabel">Edit Requirements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRequirementsForm">
                        @csrf
                        <input type="hidden" name="scholarship_id_edit" id="editScholarshipId">

                        <div id="editRequirementsContainer" class="border rounded p-3"
                            style="max-height: 500px; overflow-y: auto;">
                            <!-- load ang requirements diri dynamically -->
                        </div>

                        {{-- <button type="button" class="btn btn-sm btn-success mt-3" id="btnAddEditRequirement">
                            <i class="fa fa-plus"></i> Add Requirement
                        </button> --}}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="btnUpdateRequirements" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update Requirements</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Set Scholarship Details for Application -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="statusForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Set Scholarship Application Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="scholarshipId" name="scholarship_id">
                        <!-- Number of Slots -->
                        <div class="mb-3">
                            <label for="slots" class="form-label">Number of Slots</label>
                            <input type="number" class="form-control" id="slots" min="1" name="slots"
                                required>
                        </div>

                        <!-- Application Period -->
                        <div class="mb-3">
                            <label for="dateRange" class="form-label">Application Period</label>
                            <input type="text" class="form-control" id="dateRange" name="dateRange"
                                placeholder="Select date range" required>
                        </div>

                        <!-- Eligible Courses / Majors -->
                        <div class="mb-3">
                            <label for="eligibleCourses" class="form-label">Eligible Courses / Majors</label>
                            <select id="eligibleCourses" name="eligible_courses[]" class="form-select select2" multiple
                                required>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Eligible Year Levels -->
                        <div class="mb-3">
                            <label for="eligibleYearLevels" class="form-label">Eligible Year Levels</label>
                            <select id="eligibleYearLevels" name="eligible_year_levels[]" class="form-select select2"
                                multiple required>
                                @foreach (GENERAL::YearStanding() as $index => $yearStanding)
                                    <option value="{{ $index }}">{{ $yearStanding['Short'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Application Year and Semester -->
                        <div class="row g-2 mb-3">
                            <div class="col mb-0">
                                <label for="schApplicationSY" class="form-label">School Year</label>
                                <select class="form-select" id="schApplicationSY" name="sch_application_sy" required>
                                    <option value="">Select School Year</option>
                                    @foreach (GENERAL::SchoolYears() as $year)
                                        <option value="{{ $year }}"
                                            {{ request('sch_application_sy') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col mb-0">
                                <label for="schApplicationSem" class="form-label">School Year</label>
                                <select class="form-select" id="schApplicationSem" name="sch_application_sem" required>
                                    <option value="">Select Semester</option>
                                    @foreach (GENERAL::Semesters() as $index => $sem)
                                        <option value="{{ $index }}"
                                            {{ request('sch_application_sem') == $index ? 'selected' : '' }}>
                                            {{ $sem['Long'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Seting Release Schedule -->
    <div class="modal fade" id="setReleaseScheduleModal" tabindex="-1" aria-labelledby="setReleaseScheduleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setReleaseScheduleModalLabel">Set Release Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="releaseScheduleForm">
                    <div class="modal-body">
                        <input type="hidden" id="scholarshipId" name="scholarship_id">
                        <div class="mb-3">
                            <label for="releaseDate" class="form-label">Release Date</label>
                            <input type="date" class="form-control" id="releaseDate" name="release_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="requirements" class="form-label">Requirements</label>
                            <textarea id="requirementsEditor" name="requirements" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholarships.js.js')
    <!-- Tagify JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Date Range Picker JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <!-- Text Editor For Requirements of Claim -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/4.2.47/es2021/jodit.min.js"></script>
@endsection
