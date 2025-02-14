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
                <a href="{{ route('process-id', ['stuid' => Crypt::encryptString($student->StudentNo)]) }}">Process ID</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div class="card">
        <form id="printForm" action="{{ route('print', ['stuid' => Crypt::encryptString($student->StudentNo)]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf

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

                <input type="hidden" name="stuid" id="stuid" value="{{ Crypt::encryptString($student->StudentNo) }}">

                <div class="d-flex justify-content-center gap-4">
                    <div class="border"
                        style="width: 50%; height: 1000px; background-image: url('{{ asset('images/student/front.png') }}'); 
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
                                style="font-family: 'Poppins', sans-serif; font-size: 15px; position: relative; left: 75px; top: -38px; color: #000;">
                                {{ $defaultValues['CampusString'] }} | {{ $defaultValues['SchoolAddress'] }}
                            </p>

                            @php
                                if (!empty($student->Picture) && file_exists(public_path($student->Picture))) {
                                    $image = $student->Picture;
                                } elseif ($student->Sex === 'Male') {
                                    $image = 'images/face-male.jpg';
                                } elseif ($student->Sex === 'Female') {
                                    $image = 'images/face-female.jpg';
                                }
                            @endphp

                            <div class="profile-box" style="text-align: center; margin-top: -30px;">
                                <img src="{{ asset($image) }}" alt="Profile Picture"
                                    style="width: 330px; height: 350px; border: 0.5px solid #000;">
                            </div>
                            <div class="profile-box" style="text-align: center; margin-top: 5px;">
                                <img src="{{ file_exists(public_path('storage/student_id_signature/' . $student->StudentNo . '.png')) ? asset('storage/student_id_signature/' . $student->StudentNo . '.png') : asset('images/signature.png') }}"
                                    alt="Profile Picture" style="width: 320px; height: 85px;">
                            </div>


                            <p class="d-flex justify-content-center"
                                style="font-family: 'Poppins', sans-serif; font-size: 40px; position: relative; top: -22px; color: #000; font-weight: bold;">
                                {{ strtoupper($student->FirstName) }}
                                {{ strtoupper(Str::substr($student->MiddleName, 0, 1) . '.') }}
                                {{ strtoupper($student->LastName) }}
                            </p>

                            <p class="d-flex justify-content-center"
                                style="font-family: 'Poppins', sans-serif; font-size: 25px; position: relative;  top: -40px; color: #000;">
                                {{ $registration->Course }}
                            </p>

                            <p class="d-flex justify-content-center"
                                style="font-family: 'Poppins', sans-serif; font-size: 25px; position: relative;  top: -60px; color: #000; font-weight: bold;">
                                {{ $registration->Major }}
                            </p>

                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-uppercase mt-2"
                                        style="font-family: 'Poppins', sans-serif; font-size: 26px; position: relative; top: 10px; color: #ffffff;">
                                        Student No:
                                    </p>
                                    <p class="text-uppercase mt-4"
                                        style="font-family: 'Poppins', sans-serif; font-size: 52px; position: relative; top: -30.5px; color: #ffffff; font-weight: bold;">
                                        {{ $student->StudentNo }}
                                    </p>
                                </div>

                                <div class="col-md-6" style="left: 100px; padding-top: 18px;">
                                    <div class="card p-2"
                                        style="height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: flex-start;">
                                        <p class="text-uppercase"
                                            style="font-family: 'Poppins', sans-serif; font-size: 40px; position: relative; top: 5px; left: 6px; color: #000000; font-weight: bold;">
                                            Enrolled
                                        </p>

                                        @php
                                            $semesters = GENERAL::Semesters();
                                            $semesterShort = isset($semesters[$registration->Semester])
                                                ? $semesters[$registration->Semester]['Short']
                                                : 'N/A';

                                            $schoolYearLabel = GENERAL::setSchoolYearLabel(
                                                $registration->SchoolYear,
                                                $registration->Semester,
                                            );
                                        @endphp

                                        <p
                                            style="font-family: 'Poppins', sans-serif; font-size: 26.6px; position: relative; top: -20px; left: 6px; color: #000000; margin-bottom: -2px;">
                                            {{ $schoolYearLabel }} - {{ $semesterShort }}
                                        </p>


                                    </div>
                                </div>
                                <p class="d-flex justify-content-center"
                                    style="font-family: 'Poppins', sans-serif; font-size: 19px; position: relative;  top: -40px; color: #ffffff;">
                                    {{ $defaultValues['SchoolWebsite'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border"
                        style="width: 50%; height: 1000px; background-image: url('{{ asset('images/student/back.png') }}'); 
                    background-size: cover; background-position: center;">

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px; left: 90px; color: #000000; margin-bottom: -2px;">
                            This is to certify that the bearer, whose
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px; left: 90px; color: #000000; margin-bottom: -2px;">
                            name and photo appear in front is a
                        </p>
                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            bonafide student of SLSU.
                        </p>

                        <div class="profile-box" style="text-align: center; margin-top: 24px; margin-left: 280px;">
                            <img src="{{ asset($image) }}" alt="Profile Picture"
                                style="width: 150px; height: 150px; opacity: 0.5;">
                        </div>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: -70px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            In case of emergency,
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: -60px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ strtoupper($student->emer_name) }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: -57.5px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $student->emer_contact }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: -57.5px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $student->p_street }}, {{ $student->p_municipality }}, {{ $student->p_province }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: -20px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            Allergy/ies:
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: -20px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $student2->Allergy }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 18px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            Blood Type:
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: 18px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $student2->BloodType }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 59px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            Date issued:
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: 59px; left: 90px; color: #000000; margin-bottom: -2px;">
                            {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                        </p>

                        <p class="text-center"
                            style="font-family: 'Poppins', sans-serif; font-size: 35px; font-weight: bold; position: relative; top: 220px;  color: #000000; margin-bottom: -2px;">
                            {{ strtoupper($defaultValues['PresidentName']) }}
                        </p>

                        <div class="profile-box" style="text-align: center; margin-top: 120px;">
                            <img src="{{ asset('images/e_sig_jude.png') }}" alt="Profile Picture"
                                style="width: 60px; height: 60px;">
                        </div>

                        <p class="text-center"
                            style="font-family: 'Poppins', sans-serif; font-size: 20px; position: relative; top: 40px;  color: #000000; margin-bottom: -2px;">
                            University President
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="text-end">
                    <hr>
                    <button id="printButton" type="submit" class="btn btn-primary mt-2 mb-2">
                        <i class='bx bxs-printer me-1'></i><span>Print</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
