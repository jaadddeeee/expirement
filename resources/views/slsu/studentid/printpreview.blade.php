@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/request/student-id">School Card ID</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ url('request/process-id/' . Crypt::encryptString($student->StudentNo)) }}">Process ID</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title m-0">{{ $pageTitle }}</h5>
                <div class="ms-3">
                    {!! $headerAction ?? '' !!}
                </div>
            </div>
            <hr>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center gap-4">
                <!-- Front Image Container -->
                <div class="border"
                    style="width: 50%; height: 1000px; background-image: url('{{ asset('images/front.png') }}'); 
                    background-size: cover; background-position: center;">

                    <div class="container" style="margin-top: 88px;">
                        <h3 class=""
                            style="font-family: 'Trajan Pro', sans-serif; font-size: 34px; color: rgb(0, 0, 0); position: relative; left: 190px;">
                            Southern Leyte
                        </h3>

                        <h3 class="d-flex justify-content-center"
                            style="font-family: 'Trajan Pro', sans-serif; font-size: 28px; color: rgb(0, 0, 0); position: relative; left:18px; top: -20px;">
                            State
                            University
                        </h3>

                        <p class="d-flex justify-content-center"
                            style="font-family: 'Helvetica', sans-serif; font-size: 16px; position: relative; left: 73px; top: -38px; color: #000;">
                            Main Campus | San Roque, Sogod, Southern Leyte
                        </p>

                        <div class="profile-box" style="text-align: center; margin-top: -30px;">
                            <img src="{{ asset('images/face-male.jpg') }}" alt="Profile Picture"
                                style="width: 340px; height: 350px; border: 1px solid #000;">
                        </div>

                        <p class="d-flex justify-content-center"
                            style="font-family: 'Helvetica', sans-serif; font-size: 40px; position: relative; top: 60px; color: #000; font-weight: bold;">
                            {{ strtoupper($student->FirstName) }}
                            {{ strtoupper(Str::substr($student->MiddleName, 0, 1) . '.') }}
                            {{ strtoupper($student->LastName) }}
                        </p>

                        <p class="d-flex justify-content-center"
                            style="font-family: 'Helvetica', sans-serif; font-size: 21px; position: relative;  top: 40px; color: #000;">
                            {{ $student->Course }}
                        </p>

                        <div class="row mt-5">
                            <div class="col-md-6">
                                <p class="text-uppercase mt-4"
                                    style="font-family: 'Helvetica', sans-serif; font-size: 20px; position: relative; top: 110px; color: #ffffff;">
                                    Student No:
                                </p>
                                <p class="text-uppercase mt-4"
                                    style="font-family: 'Helvetica', sans-serif; font-size: 50px; position: relative; top: 76px; color: #ffffff; font-weight: bold;">
                                    {{ $student->StudentNo }}
                                </p>
                            </div>

                            <div class="col-md-6" style="left: 100px; padding-top: 129px;">
                                <div class="card p-2"
                                    style="height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: flex-start;">
                                    <p class="text-uppercase"
                                        style="font-family: 'Helvetica', sans-serif; font-size: 40px; position: relative; top: 10px; left: 10px; color: #000000; font-weight: bold;">
                                        Enrolled
                                    </p>

                                    @php
                                        $semesters = GENERAL::Semesters();
                                        $semesterShort = $semesters[$registration->Semester]
                                            ? $semesters[$registration->Semester]['Short']
                                            : 'N/A';
                                    @endphp

                                    <p
                                        style="font-family: 'Helvetica', sans-serif; font-size: 25px; position: relative; top: -14px; left: 10px; color: #000000; margin-bottom: -2px;">
                                        {{ $registration->SchoolYear }} - {{ $semesterShort }}
                                    </p>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Back Image Container -->
                <div class="border"
                    style="width: 50%; height: 1000px; background-image: url('{{ asset('images/back.png') }}'); 
                    background-size: cover; background-position: center;">
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="text-end">
                <hr>
                <a href="{{ route('student.print', Crypt::encryptString($student->StudentNo)) }}"
                    class="btn btn-primary mt-2 mb-2">
                    <i class='bx bxs-printer me-1'></i><span>Print</span>
                </a>
            </div>
        </div>
    </div>
@endsection
