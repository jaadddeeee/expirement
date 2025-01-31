@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item active">
                <a href="javascript:void(0);">{{ $pageTitle }}</a>
            </li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 flex-grow-1">School ID Card</h5>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-3 d-flex align-items-center">
                    <label for="pageSize" class="me-2 mb-0">Show</label>
                    <select id="pageSize" class="form-select" style="width: auto; display: inline-block;">
                        <option value="10">10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label class="ms-2 mb-0">entries</label>
                </div>
                <div class="col-md-6 mb-3 d-flex justify-content-end align-items-center">
                    <label for="searchInput" class="me-2 mb-0">Search</label>
                    <input class="form-control" type="search" placeholder="Search Student ID or Student Name"
                        id="searchInput" style="max-width: 300px;" />
                </div>
            </div>
        </div>
        <div class="text-nowrap mb-4">
            <table id="tableLogs" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>STUDENT ID</th>
                        <th>STUDENT NAME</th>
                        <th>SEX</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @php $id = 1; @endphp
                    @foreach ($student as $students)
                        <tr>
                            <td>{{ $id++ }}</td>
                            <td>{{ $students->StudentNo }}</td>
                            <td>
                                {{ $students->FirstName }}
                                {{ $students->MiddleName ? Str::substr($students->MiddleName, 0, 1) . '.' : '' }}
                                {{ $students->LastName }}
                                {{-- {{ $students->Suffix ? $students->Suffix . '.' : '' }} --}}
                            </td>
                            <td>{{ $students->Sex ? Str::substr($students->Sex, 0, 1) : '' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item d-flex align-items-center"
                                            href="{{ url('request/process-id/' . Crypt::encryptString($students->StudentNo)) }}">
                                            <i class="bx bxs-id-card me-2"></i>
                                            <span>Process ID</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
