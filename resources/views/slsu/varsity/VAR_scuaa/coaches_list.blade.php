@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:void(0);">{{ $page }}</a>
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between pb-1">
                    <div class="header-title">
                        <h4 class="card-title" id="pageTitle">{{ $pageTitle ?? 'List' }}</h4>
                        <h5 class="card-title">{{ $title_coach ?? 'List' }}</h5>
                    </div>
                    <div class="card-action d-flex align-items-center gap-2">
                        <form method="GET" action="{{ route('scuaa-coaches') }}">
                            @csrf
                            <div class="d-flex align-items-center gap-2">
                                <input type="search" name="searchCoach" id="searchCoach" class="form-control form-control-sm"
                                    placeholder="Search" value="{{ request('searchCoach') }}">
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
                                <button type="submit" class="btn btn-warning btn-sm" id="btn-filter"><i
                                        class='bx bxs-filter-alt'></i></button>
                            </div>
                            <div class="d-flex mt-1 gap-2 justify-content-end">
                                <a href="" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#scuaaHistory"><i class='bx bx-history'></i></a>
                                {!! $headerAction ?? '' !!}
                            </div>
                        </form>
                    </div>
                </div>
                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <td class = "text-nowrap">#</td>
                                    <th class="text-nowrap">Coach Name</th>
                                    <th class="text-nowrap">Coach Event</th>
                                    <th class="text-nowrap">SchoolYear</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="data">
                                @include('_partials.scuaa-coach-table')
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
                                                {{ ($Coaches->currentPage() - 1) * $Coaches->perPage() + 1 }}
                                                -
                                                {{ min($Coaches->currentPage() * $Coaches->perPage(), $Coaches->total()) }}
                                            </span>
                                            of
                                            <span class="text-dark">
                                                {{ $Coaches->total() }}
                                            </span>
                                        </p>
                                    </div>
                            
                                    {{-- Pagination buttons --}}
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            {{-- First page button --}}
                                            <li class="page-item {{ $Coaches->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $Coaches->url(1) }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to first page">
                                                    <i class="bx bx-chevrons-left"></i>
                                                </a>
                                            </li>

                                            {{-- Previous page button --}}
                                            <li class="page-item {{ $Coaches->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $Coaches->previousPageUrl() }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to previous page">
                                                    <i class="bx bx-chevron-left"></i>
                                                </a>
                                            </li>

                                            {{-- Next page button --}}
                                            <li class="page-item {{ !$Coaches->hasMorePages() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $Coaches->nextPageUrl() }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to next page">
                                                    <i class="bx bx-chevron-right"></i>
                                                </a>
                                            </li>

                                            {{-- Last page button --}}
                                            <li class="page-item {{ !$Coaches->hasMorePages() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $Coaches->url($Coaches->lastPage()) }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to last page">
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

    @include('slsu.varsity.VAR_scuaa.modal_scuaa')

@endsection

@section('page-script')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @include('slsu.varsity.VAR_scuaa.js')

@endsection
