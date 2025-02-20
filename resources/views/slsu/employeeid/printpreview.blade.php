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
                <a href="{{ route('emp_process-id', ['emid' => Crypt::encryptString($employee->id)]) }}">Process
                    ID</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div class="card">
        <form id="printForm" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="emid" id="emid" value="{{ Crypt::encryptString($employee->id) }}">

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
                    <div class="border"
                        style="width: 50%; height: 1000px; background-image: url('{{ asset('images/employee/front.png') }}'); 
                    background-size: cover; background-position: center;">

                        <div class="container" style="margin-top: 78px;">
                            <div class="text-content" style="position: relative; left: 190px;">
                                <h3
                                    style="font-family: 'Trajan Pro', sans-serif; font-size: 34px; color: rgb(0, 0, 0); position: relative; left: 0;">
                                    Southern Leyte
                                </h3>

                                <h3
                                    style="font-family: 'Trajan Pro', sans-serif; font-size: 28px; color: rgb(0, 0, 0); position: relative; left: 0; top: -20px;">
                                    State University
                                </h3>

                                <p
                                    style="font-family: 'Poppins', sans-serif; font-size: 15px; position: relative; left: 0; top: -38px; color: #000;">
                                    {{ $defaultValues['CampusString'] }} | {{ $defaultValues['SchoolAddress'] }}
                                </p>
                            </div>

                            @php
                                $decryptedSex = AES::decrypt($employee->Sex);
                                if (!empty($employee->profilephoto)) {
                                    $image = $employee->profilephoto;
                                } elseif ($decryptedSex === 'Male') {
                                    $image = 'images/face-male.jpg';
                                } elseif ($decryptedSex === 'Female') {
                                    $image = 'images/face-female.jpg';
                                } elseif ($decryptedSex === '') {
                                    $image = 'images/user.png';
                                }
                            @endphp

                            <div class="profile-box" style="text-align: center; margin-top: -30px;">
                                <img src="{{ asset($image) }}" alt="Profile Picture"
                                    style="width: 330px; height: 380px; border: 0.5px solid #000;">
                            </div>

                            <div class="profile-box" style="text-align: center; position: relative; top: 30px;">
                                <img src="{{ asset('images/signature.png') }}" alt="Profile Picture"
                                    style="width: 320px; height: 85px;">
                            </div>

                            <p class="d-flex justify-content-center"
                                style="font-family: 'Poppins', sans-serif; font-size: 50px; position: relative; top: 10px; color: #000; font-weight: bold; text-decoration: underline;">
                                <span style="position: relative; top: 5px;">
                                    {{ strtoupper($employee->FirstName) }}
                                    {{ strtoupper(Str::substr($employee->MiddleName, 0, 1) . '.') }}
                                    {{ strtoupper($employee->LastName) }}
                                </span>
                            </p>

                            <p class="d-flex justify-content-center"
                                style="font-family: 'Poppins', sans-serif; font-size: 35px; position: relative; top: -15px; color: #000;">
                                Staff
                            </p>

                            <p class="d-flex justify-content-center"
                                style="font-family: 'Poppins', sans-serif; font-size: 25px; position: relative;  top: -60px; color: #000; font-weight: bold;">

                            </p>

                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-uppercase mt-2"
                                        style="font-family: 'Poppins', sans-serif; font-size: 26px; position: relative; top: -10px; color: #ffffff;">
                                        Employee No.
                                    </p>
                                    <p class="text-uppercase mt-4"
                                        style="font-family: 'Poppins', sans-serif; font-size: 52px; position: relative; top: -50.5px; color: #ffffff; font-weight: bold;">
                                        {{ $employee->AgencyNumber ? $employee->AgencyNumber : 'N/A' }}
                                    </p>
                                </div>

                                <p class="d-flex justify-content-center"
                                    style="font-family: 'Poppins', sans-serif; font-size: 19px; position: relative;  top: -60px; color: #000000;">
                                    www.southernleytestateu.edu.ph
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border"
                        style="width: 50%; height: 1000px; background-image: url('{{ asset('images/employee/back.png') }}'); 
                    background-size: cover; background-position: center;">

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px; left: 90px; color: #000000; margin-bottom: -2px;">
                            This is to certify that the bearer of this
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px; left: 90px; color: #000000; margin-bottom: -2px;">
                            identification card, whose name and photo
                        </p>
                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            appear in front, is an employee of
                        </p>
                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 50px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            Southern Leyte State University.
                        </p>

                        <div class="profile-box" style="text-align: center; margin-top: 24px; margin-left: 360px;">
                            <img src="{{ asset($image) }}" alt="Profile Picture"
                                style="width: 120px; height: 120px; opacity: 0.5;">
                        </div>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: -70px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            In case of emergency,
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: -60px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $employee2->name ?? 'N/A' }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: -57.5px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $employee2->address ?? 'N/A' }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: -57.5px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $employee2->contact ?? 'N/A' }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: -20px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            Allergy/ies:
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: -20px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $employee->Allergies ?? 'None' }}
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; position: relative; top: 18px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            Blood Type:
                        </p>

                        <p
                            style="font-family: 'Poppins', sans-serif; font-size: 23px; font-weight: bold; position: relative; top: 18px ; left: 90px ; color: #000000; margin-bottom: -2px;">
                            {{ $employee->BloodType ?? 'N/A' }}
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
                            style="font-family: 'Poppins', sans-serif; font-size: 35px; font-weight: bold; position: relative; top: 220px; color: #000000; margin-bottom: -2px; text-decoration: underline;">
                            <span style="position: relative; top: -5px;">JUDE A. DUARTE, DPA</span>
                        </p>

                        <div class="profile-box" style="text-align: center; margin-top: 115px;">
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

@section('page-script')
    @include('slsu.employeeid.js')
@endsection
