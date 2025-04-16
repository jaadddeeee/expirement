@extends('layouts.blankLayout')

@section('title', $pageTitle)

@section('content')
    <div class="container py-4">
        <!-- Header with Logo -->
        <div class="text-center mb-5">
            <img src="{{ asset('images/logo/updated_logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 100px;">
            <h2 class="mt-3 fw-bold" style="color: #66a6ea">MAIN CAMPUS | SCHOLARSHIP OPPORTUNITIES</h2>
            <p class="text-muted">Explore available scholarships for students</p>
        </div>

        <!-- Campus Selector Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <h5 class="card-title mb-0">Select Your Campus</h5>
                    </div>
                    <div class="col-md-6">
                        <select id="campusSelect" class="form-select">
                            <option value="main" selected>Main Campus</option>
                            <option value="campus1">Campus 1</option>
                            <option value="campus2">Campus 2</option>
                            <option value="campus3">Campus 3</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scholarships Section -->
        @if ($scholarships->isEmpty())
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i> Currently there are no available scholarships.
            </div>
        @else
            <div class="row g-4 align-items-start">
                <!-- Scholarship List Column -->
                <div class="col-lg-4 pt-0">
                    <div class="card shadow-sm rounded bg-light h-100">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h5 class="mb-0 fw-semibold text-center" style="color: #66a6ea">List of Available Scholarships
                            </h5>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($scholarships as $index => $scholarship)
                                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-capitalize {{ $index === 0 ? 'active' : '' }}"
                                    id="list-scholarship-{{ $index }}" data-bs-toggle="list"
                                    href="#scholarship-{{ $index }}">
                                    <span>
                                        <strong>{{ $scholarship->sch_name }}</strong>
                                        <small class="d-block text-muted">{{ $scholarship->sch_acronym }}</small>
                                        <small class="d-block text-muted">
                                            Start:
                                            <strong>
                                                {{ \Carbon\Carbon::parse($scholarship->start_date)->format('M d, Y') }}
                                            </strong><br>
                                            Deadline:
                                            <strong>
                                                {{ \Carbon\Carbon::parse($scholarship->deadline_date)->format('M d, Y') }}
                                            </strong>
                                        </small>
                                    </span>
                                    <span class="badge rounded-pill bg-primary text-white">
                                        {{ $scholarship->slots }} slot{{ $scholarship->slots > 1 ? 's' : '' }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Scholarship Details Column -->
                <div class="col-lg-8">
                    <div class="tab-content h-100 pt-0 pe-0 ps-0">
                        @foreach ($scholarships as $index => $scholarship)
                            <div class="tab-pane fade h-100 {{ $index === 0 ? 'show active' : '' }}"
                                id="scholarship-{{ $index }}">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header text-white pb-0">
                                        <h4 class="card-title mb-0 text-capitalize" style="color: #66a6ea">
                                            <strong>{{ $scholarship->sch_name }}</strong>
                                        </h4>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <span
                                                class="badge bg-secondary text-white me-1">{{ $scholarship->sch_acronym }}</span>
                                            <span class="badge bg-success">{{ $scholarship->slots }} available</span>
                                        </div>

                                        <h5 class="mt-4" style="color: #66a6ea">
                                            <i class="bx bx-notepad me-2"></i> Description
                                        </h5>
                                        <p class="card-text">{{ $scholarship->description ?? 'No description provided.' }}
                                        </p>

                                        <hr class="mb-4">

                                        <h5 style="color: #66a6ea">
                                            <i class="bx bx-list-check me-2"></i>Requirements
                                        </h5>
                                        @if ($scholarship->requirements->isEmpty())
                                            <p class="card-text">No specific requirements listed.</p>
                                        @else
                                            <div class="list-group list-group-flush">
                                                @foreach ($scholarship->requirements as $requirement)
                                                    <a href="javascript:void(0);"
                                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                                        <span class="badge bg-secondary rounded-pill me-3">
                                                            {{ $requirement->quantity }}
                                                        </span>
                                                        {{ $requirement->sch_requirements }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="mt-4">
                                            <button class="btn btn-sm btn-primary me-2">Apply Now</button>
                                            <button class="btn btn-sm btn-outline-secondary">Learn More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .list-group-item {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .list-group-item.active {
            border-left-color: var(--bs-primary);
            background-color: var(--bs-primary) !important;
            /* Changed to primary color */
            color: white !important;
            /* Changed text to white */
        }

        .list-group-item.active .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
            /* Lighter text for muted elements */
        }

        .list-group-item .badge {
            background-color: var(--bs-primary);
            color: white;
            transition: all 0.3s ease;
        }

        .list-group-item.active .badge {
            background-color: white !important;
            color: var(--bs-primary) !important;
        }

        .list-group-item:hover:not(.active) {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        .tab-pane {
            transition: opacity 0.4s ease-in-out;
        }
    </style>
@endsection
