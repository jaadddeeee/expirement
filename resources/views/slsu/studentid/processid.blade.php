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
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div class="card">
        <form action="{{ url('request/print-preview/' . Crypt::encryptString($student->StudentNo)) }}" method="POST"
            enctype="multipart/form-data">
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
                <div class="row">
                    <!-- Left Side: Profile Picture & Signature -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <!-- Profile Picture Container with Zoom Controls -->
                            <div class="image-container d-block border mx-auto"
                                style="position: relative; overflow: hidden; width: 100%; height: 700px;">

                                @php
                                    $image = '';
                                    if ($student->Sex === 'Male') {
                                        $image = 'face-male.jpg';
                                    } elseif ($student->Sex === 'Female') {
                                        $image = 'face-female.jpg';
                                    }
                                @endphp

                                <img src="{{ asset('images/' . $image) }}" id="previewProfile" alt="Profile Picture"
                                    class="d-block mx-auto mb-3"
                                    style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">


                                <div class="zoom-controls" style="position: absolute; bottom: 10px; right: 10px;">
                                    <button type="button" class="btn btn-danger zoom-btn" id="zoomOutProfile">-</button>
                                    <button type="button" class="btn btn-primary zoom-btn" id="zoomInProfile">+</button>
                                </div>
                            </div>
                            <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                            <input class="form-control" type="file" id="profilePicture" name="profilePicture"
                                accept="image/*">
                        </div>

                        <div class="mb-3">
                            <!-- Signature Container with Zoom Controls -->
                            <div class="image-container d-block border mx-auto"
                                style="position: relative; overflow: hidden; width: 100%; height: 100px;">
                                <img src="{{ asset('images/signature.png') }}" id="previewSignature" src=""
                                    alt="Signature" class="d-block mx-auto mb-3"
                                    style="width: 100%; height: 100%; object-fit: contain; transition: transform 0.3s;">
                                <div class="zoom-controls" style="position: absolute; bottom: 10px; right: 10px;">
                                    <button type="button" class="btn btn-danger zoom-btn" id="zoomOutSignature">-</button>
                                    <button type="button" class="btn btn-primary zoom-btn" id="zoomInSignature">+</button>
                                </div>
                            </div>
                            <label for="signature" class="form-label">Upload Signature</label>
                            <input class="form-control" type="file" id="signature" name="signature" accept="image/*">
                        </div>


                        @php
                            $semester = '';
                            if ($registration->Semester === 1) {
                                $semester = $registration->Semester . 'st';
                            } elseif ($registration->Semester === 2) {
                                $semester = $registration->Semester . 'nd';
                            }
                        @endphp

                        <div class="mb-0">
                            <label for="province" class="form-label">School Year - Semester</label>
                            <input type="text" name="province" id="province" class="form-control"
                                placeholder="Enter School Year and Semester"
                                value="{{ $registration->SchoolYear }} - {{ $semester }}">
                        </div>

                    </div>

                    <!-- Right Side: Additional Fields -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="studentName" class="form-label">Name</label>
                            <br>
                            <h4 class="text-uppercase">{{ $student->FirstName }}
                                {{ Str::substr($student->MiddleName, 0, 1) . '.' }}
                                {{ $student->LastName }}</h4>
                        </div>

                        <div class="mb-3">
                            <label for="studentID" class="form-label">Student ID</label>
                            <br>
                            <h4 class="text-uppercase">{{ $student->StudentNo }}</h4>
                        </div>

                        <div class="mb-3">
                            <label for="course" class="form-label">Course</label>
                            <h4>{{ $student->Course }}</h4>
                        </div>

                        <div class="mb-4">
                            <label for="specialization" class="form-label">Specialization</label>
                            <h4>{{ $student->major }}</h4>
                        </div>

                        <label class="form-label mb-3" style="color: #39DA8A; ">PERSONAL INFORMATION</label>
                        <div class="mb-3">
                            <label for="blood_type" class="form-label">BLOOD TYPE</label>
                            <input type="text" name="blood_type" id="blood_type" class="form-control"
                                placeholder="Enter Blood Type" value="{{ $student2->BloodType }}">
                        </div>

                        <div class="mb-4">
                            <label for="allergy" class="form-label">Allergy/Allergies</label>
                            <input type="text" name="allergy" id="allergy" class="form-control"
                                placeholder="Enter Allergy/Allergies" value="{{ $student2->Allergy }}">
                        </div>

                        <label class="form-label mb-3" style="color: #39DA8A; ">EMERGENCY CONTACT INFORMATION</label>
                        <div class="mb-3">
                            <label for="contact_name" class="form-label">Contact Name</label>
                            <input type="text" name="contact_name" id="contact_name" class="form-control"
                                placeholder="Enter Contact Name" value="{{ $student->emer_name }}">
                        </div>
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="contact" name="contact_number" id="contact_number" class="form-control"
                                placeholder="Enter Contact Number" value="{{ $student->emer_contact }}">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="barangay" class="form-label">Barangay</label></label>
                                <input type="text" name="barangay" id="barangay" class="form-control"
                                    placeholder="Enter Barangay" value="{{ $student->p_street }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="municipality" class="form-label">Municipality</label>
                                <input type="text" name="municipality" id="municipality" class="form-control"
                                    placeholder="Enter Municipality" value="{{ $student->p_municipality }}">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="province" class="form-label">Province</label>
                                <input type="text" name="province" id="province" class="form-control"
                                    placeholder="Enter Province" value="{{ $student->p_province }}">
                            </div>
                        </div>
                        <div class="row">
                            <label class="form-label mb-3" style="color: #39DA8A;">Payment</label>
                            <div class="col-md-6 mb-3">
                                <label for="or_number" class="form-label">OR No.</label>
                                <input type="text" name="or_number" id="or_number" class="form-control"
                                    placeholder="Enter OR Number">
                            </div>
                            <div class="col-md-6">
                                <label for="date_paid" class="form-label">Date Paid</label>
                                <input type="date" name="date_paid" id="date_paid" class="form-control"
                                    value="{{ now()->toDateString() }}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    <hr>
                    <button type="submit" class="btn btn-primary mt-2 mb-2"><i
                            class='bx bx-search-alt-2 me-1'></i><span>Print
                            Preview</span></button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Profile Picture Zoom In and Out
        let zoomLevelProfile = 1;
        document.getElementById('zoomInProfile').addEventListener('click', function() {
            zoomLevelProfile += 0.1;
            document.getElementById('previewProfile').style.transform = 'scale(' + zoomLevelProfile + ')';
        });
        document.getElementById('zoomOutProfile').addEventListener('click', function() {
            if (zoomLevelProfile > 0.1) {
                zoomLevelProfile -= 0.1;
                document.getElementById('previewProfile').style.transform = 'scale(' + zoomLevelProfile + ')';
            }
        });

        // Signature Zoom In and Out
        let zoomLevelSignature = 1;
        document.getElementById('zoomInSignature').addEventListener('click', function() {
            zoomLevelSignature += 0.1;
            document.getElementById('previewSignature').style.transform = 'scale(' + zoomLevelSignature + ')';
        });
        document.getElementById('zoomOutSignature').addEventListener('click', function() {
            if (zoomLevelSignature > 0.1) {
                zoomLevelSignature -= 0.1;
                document.getElementById('previewSignature').style.transform = 'scale(' + zoomLevelSignature + ')';
            }
        });

        // Image Preview on file selection
        document.getElementById('profilePicture').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('previewProfile');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        document.getElementById('signature').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('previewSignature');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
@endsection
