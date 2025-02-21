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

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="header-title">
                        <h4 class="card-title">{{ $pageTitle ?? 'List' }}</h4>
                    </div>
                    <div class="d-flex">
                        <div class="me-2">
                            {!! $headerAction ?? '' !!}
                        </div>
                        <div>
                            <input type="text" id="searchStudent" class="form-control form-control-sm"
                                placeholder="Search Student..." />
                        </div>
                    </div>
                </div>
                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th class="text-nowrap" style="width: 75px">#</th>
                                    <th class="text-nowrap" style="width: 100px">StudentNo</th>
                                    <th class="text-nowrap" style="width: 150px">Student Name</th>
                                    <th class="text-nowrap" style="width: 100px">Sex</th>
                                    <th class="text-nowrap" style="width: 250px">Course</th>
                                    <th class="text-nowrap" style="width: 250px">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($students as $student)
                                    <tr>
                                        <td class="text-nowrap">{{ $loop->iteration }}</td>
                                        <td class="text-nowrap">{{ $student->StudentNo }}</td>
                                        <td class="text-nowrap">{{ $student->LastName ?? 'N/A' }},
                                            {{ $student->FirstName ?? '' }}</td>
                                        <td class="text-nowrap">{{ $student->Sex ?? 'N/A' }}</td>
                                        <td class="text-nowrap">{{ $student->Course ?? 'N/A' }}</td>
                                        <td class="text-nowrap">
                                            <!-- Add Button Icon -->
                                            <i class="fa fa-plus-circle text-success me-2 add-scholar"
                                                style="cursor: pointer;" data-studentno="{{ $student->StudentNo }}"
                                                data-studentname="{{ $student->LastName }}, {{ $student->FirstName }}"
                                                title="Add">
                                            </i>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                            {{ $students->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Adding New Scholar --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddScholar" aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class="fa fa-plus"></i>Add Scholar</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>
        <form id="frmAddScholar">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <label>Student NO.</label>
                <input type="text" name="studentNo" class="mb-4 form-control" readonly>

                <label>Student Name</label>
                <input type="text" name="studentName" class="mb-4 form-control" readonly>

                <label>Select Scholarship</label>
                <select class="form-select mb-4" name="selectScholarship" id="selectScholarship">
                    <option value="" disabled selected>Select Scholarship</option>
                    @foreach ($scholarships as $scholarship)
                        <option value="{{ $scholarship->id }}">{{ $scholarship->sch_name }}</option>
                    @endforeach
                </select>

                <label>Date Awarded</label>
                <input class="form-control mb-4" type="date" value="2021-06-18" id="dateAwarded" />

                <label>School Year</label>
                <input type="text" name="schoolYear" class="mb-4 form-control" placeholder="">

                <label>Semester</label>
                <input type="text" name="semester" class="mb-4 form-control" placeholder="">

                <label>Bank Account No.</label>
                <input type="text" name="bankAccNo" class="mb-4 form-control" placeholder="">

                <button class="mb-3 btn btn-primary" id="btnAddScholar">Add Scholar</button>
                <div id="msg"></div>
            </div>
        </form>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholar.js')
@endsection
