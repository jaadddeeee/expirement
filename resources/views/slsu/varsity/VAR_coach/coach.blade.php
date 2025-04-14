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
                        <form method="GET" action="{{ route('coaches') }}">
                            <div class="d-flex align-items-center gap-2">
                                <input type="search" name="search" id="search" class="form-control form-control-sm"
                                    placeholder="Search">
                                <select name="filterCT" id="filterCT" class="form-select form-select-sm">
                                    <option hidden value="0">Select Coach Type</option>
                                    @foreach (GENERAL::CoachType() as $index => $type)
                                        <option value = "{{ $index }}">{{ $type['Type'] }}</option>
                                    @endforeach
                                </select>
                                @if (auth()->user()->AllowSuper == 1)
                                    <select name="filterCampus" id="filterCampus" class="form-select form-select-sm w-auto">
                                        <option hidden value="0">Select campus</option>
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
                                <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#modalCoach" aria-controls="offcanvasBackdrop">New</a>
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
                                    <th class="text-nowrap">Coach Type</th>
                                    <th class="text-nowrap">Coach Event</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="data">
                                @include('_partials.coach-table')
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
                                                {{ ($coaches->currentPage() - 1) * $coaches->perPage() + 1 }}
                                                -
                                                {{ min($coaches->currentPage() * $coaches->perPage(), $coaches->total()) }}
                                            </span>
                                            of
                                            <span class="text-dark">
                                                {{ $coaches->total() }}
                                            </span>
                                        </p>
                                    </div>
                            
                                    {{-- Pagination buttons --}}
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            {{-- First page button --}}
                                            <li class="page-item {{ $coaches->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link btn btn-primary" href="{{ $coaches->url(1) }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to first page">
                                                    <i class="bx bx-chevrons-left"></i>
                                                </a>
                                            </li>

                                            {{-- Previous page button --}}
                                            <li class="page-item {{ $coaches->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link btn btn-primary" href="{{ $coaches->previousPageUrl() }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to previous page">
                                                    <i class="bx bx-chevron-left"></i>
                                                </a>
                                            </li>

                                            {{-- Next page button --}}
                                            <li class="page-item {{ !$coaches->hasMorePages() ? 'disabled' : '' }}">
                                                <a class="page-link btn btn-primary" href="{{ $coaches->nextPageUrl() }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to next page">
                                                    <i class="bx bx-chevron-right"></i>
                                                </a>
                                            </li>

                                            {{-- Last page button --}}
                                            <li class="page-item {{ !$coaches->hasMorePages() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $coaches->url($coaches->lastPage()) }}&rowsPerPage={{ request('rowsPerPage', 5) }}" aria-label="Go to last page">
                                                    <i class="bx bx-chevrons-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm">
                                    {{ $coaches->appends(request()->query())->links() }}
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('slsu.varsity.VAR_coach.modal_coach')
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('slsu.varsity.VAR_coach.js')

@endsection
