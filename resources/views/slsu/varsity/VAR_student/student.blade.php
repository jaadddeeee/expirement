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
                <div class="card-header d-flex justify-content-between pb-1">
                    <div class="header-title">
                        <h4 class="card-title">{{ $pageTitle ?? 'List' }}</h4>
                    </div>
                    <div class="card-action d-flex align-items-center gap-2">
                        <form method="GET" action="/varsity/varsity">
                            @csrf
                            <div class="d-flex align-items-center gap-2">
                                <input type="search" name="search" id="search" class="form-control form-control-sm"
                                    placeholder="Search" value="{{ request('search') }}">
                                <select name="filterEvent" id="filterEvent" class="form-select form-select-sm w-auto">
                                    <option value="0">Select Event</option>
                                    @foreach ($Events as $Event)
                                        <option value="{{ $Event->id }}"
                                            {{ request('filterEvent') == $Event ? 'selected' : '' }}>{{ $Event->event }}
                                        </option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm w-auto" name="filterSchoolYear"
                                    id="filterSchoolYear">
                                    <option value="0">Select SchoolYear</option>
                                    @foreach (GENERAL::SchoolYears() as $index => $sy)
                                        <option value="{{ $sy }}"
                                            {{ request('filterSchoolYear') == $sy ? 'selected' : '' }}>{{ $sy }}
                                        </option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm w-auto" name="filterSemester" id="filterSemester">
                                    <option value="0">Select Semester</option>
                                    @foreach (GENERAL::Semesters() as $index => $sem)
                                        <option value="{{ $index }}"
                                            {{ request('filterSemester') == $index ? 'selected' : '' }}>
                                            {{ $sem['Long'] }}</option>
                                    @endforeach
                                </select>
                                @if (auth()->user()->AllowSuper == 1)
                                    <select name="filterCampus" id="filterCampus" class="form-select form-select-sm w-auto">
                                        @foreach (GENERAL::Campuses() as $index => $campus)
                                            <option value="{{ $index }}" <?= $index == $Campus ? 'Selected' : '' ?>>
                                                {{ $campus['Campus'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <button type="submit" class="btn btn-warning btn-sm" id="btn-filter"><i
                                        class='bx bxs-filter-alt'></i></button>
                            </div>
                            <div class="d-flex mt-1 gap-2 justify-content-end">
                                {!! $headerAction ?? '' !!}
                                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#modalVarsity" aria-controls="offcanvasBackdrop">Add Varsity</a>
                            </div>
                        </form>
                    </div>
                </div>
                @if (session('campus') == 'SG')
                    <div class="d-flex justify-content-start container-xxl gap-2">
                        <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" id="btn-list">Add SCUAA list <span id="selected-row" style="--bs-text-opacity: .5;"></span></a>
                    </div>
                @endif

                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    @if (session('campus') == 'SG')
                                        <td class="text-nowrap "><input class="form-check-input" type="checkbox"
                                                id="select-all"></td>
                                    @else
                                        <td class="text-nowrap">#</td>
                                    @endif
                                    <th class="text-nowrap">Varsity Name</th>
                                    <th class="text-nowrap">Varsity Event</th>
                                    <th class="text-nowrap">SY/Sem</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="data"    >
                                @include('_partials.var_student-table')
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between mt-2">
                            <form method="GET">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="rowsPerPage" class="form-label d-none d-sm-block mt-2 pe-2">Rows per page</label>
                                    <select id="rowsPerPage" name="rowsPerPage" class="form-select form-select-sm w-auto"
                                        onchange="this.form.submit()">
                                        @foreach ([5, 10, 25, 50] as $pageSize)
                                            <option value="{{ $pageSize }}" {{ request('rowsPerPage', 5) == $pageSize ? 'selected' : '' }}>
                                                {{ $pageSize }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                {{-- Page number information and pagination buttons --}}
                                <div class="d-flex align-items-center gap-3">
                                    {{-- Page number information --}}
                                    <div id="page-info" class="text-muted text-sm">
                                        <p class="mb-0" aria-live="polite">
                                            <span class="text-dark">
                                                {{ ($varsities->currentPage() - 1) * $varsities->perPage() + 1 }}
                                                -
                                                {{ min($varsities->currentPage() * $varsities->perPage(), $varsities->total()) }}
                                            </span>
                                            of
                                            <span class="text-dark">
                                                {{ $varsities->total() }}
                                            </span>
                                        </p>
                                    </div>
                            
                                    {{-- Pagination buttons --}}
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            {{-- First page button --}}
                                            <li class="page-item {{ $varsities->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $varsities->url(1) }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to first page">
                                                    <i class="bx bx-chevrons-left"></i>
                                                </a>
                                            </li>

                                            {{-- Previous page button --}}
                                            <li class="page-item {{ $varsities->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $varsities->previousPageUrl() }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to previous page">
                                                    <i class="bx bx-chevron-left"></i>
                                                </a>
                                            </li>

                                            {{-- Next page button --}}
                                            <li class="page-item {{ !$varsities->hasMorePages() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $varsities->nextPageUrl() }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to next page">
                                                    <i class="bx bx-chevron-right"></i>
                                                </a>
                                            </li>

                                            {{-- Last page button --}}
                                            <li class="page-item {{ !$varsities->hasMorePages() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $varsities->url($varsities->lastPage()) }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to last page">
                                                    <i class="bx bx-chevrons-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('slsu.varsity.VAR_student.modal_student')

@endsection

@section('page-script')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('slsu.varsity.VAR_student.js')

@endsection
