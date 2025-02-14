@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)
<style>
    .loading-spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.3);
        z-index: 9999;
    }

    .dot-container {
        display: flex;
        justify-content: space-between;
        width: 70px;
    }

    .dot {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #3498db;
        opacity: 0;
        animation: dot-chase 1.5s infinite;
    }

    @keyframes dot-chase {
        0% {
            opacity: 0;
            transform: translateY(0);
        }

        30% {
            opacity: 1;
            transform: translateY(-10px);
        }

        60% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0;
            transform: translateY(0);
        }
    }

    .dot:nth-child(1) {
        animation-delay: 0s;
    }

    .dot:nth-child(2) {
        animation-delay: 0.3s;
    }

    .dot:nth-child(3) {
        animation-delay: 0.6s;
    }
</style>
@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/school-id/student-list">School Card ID</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div id="loadingSpinner" class="loading-spinner" style="display: none;">
        <div class="dot-container">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <div class="card">
        <form id="processIdForm" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="stuid" id="stuid" value="{{ Crypt::encryptString($student->StudentNo) }}">

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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="image-container d-block border mx-auto"
                                style="position: relative; overflow: hidden; width: 100%; height: 700px;">

                                @php
                                    if (!empty($student->Picture) && file_exists(public_path($student->Picture))) {
                                        $image = $student->Picture;
                                    } elseif ($student->Sex === 'Male') {
                                        $image = 'images/face-male.jpg';
                                    } elseif ($student->Sex === 'Female') {
                                        $image = 'images/face-female.jpg';
                                    }
                                @endphp

                                <img src="{{ asset($image) }}" id="previewProfile" alt="Profile Picture"
                                    class="d-block mx-auto mb-3"
                                    style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">

                                <div class="crop-control" style="position: absolute; bottom: 10px; right: 10px;">
                                    <button type="button" class="btn btn-primary crop-btn d-flex align-items-center"
                                        id="cropProfile">
                                        <i class="bx bx-crop me-1"></i>
                                    </button>
                                </div>
                            </div>

                            <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                            <input class="form-control" type="file" id="profilePicture" name="profilePicture"
                                accept="image/*">
                        </div>
                        <div class="mb-3">
                            <div class="image-container d-block border mx-auto"
                                style="position: relative; overflow: hidden; width: 100%; height: 100px;">
                                @php
                                    $signaturePath = 'storage/student_id_signature/' . $student->StudentNo . '.png';

                                    if (file_exists(public_path($signaturePath))) {
                                        $signature = $signaturePath;
                                    } else {
                                        $signature = 'images/signature.png';
                                    }
                                @endphp

                                <img src="{{ asset($signature) }}" id="previewSignature" alt="Signature"
                                    class="d-block mx-auto mb-3"
                                    style="width: 100%; height: 100%; object-fit: contain; transition: transform 0.3s;">

                                <div class="crop-controls" style="position: absolute; bottom: 10px; right: 10px;">
                                    <button type="button" class="btn btn-primary crop-btn d-flex align-items-center"
                                        id="cropSignature">
                                        <i class="bx bx-crop me-1"></i>
                                    </button>

                                </div>
                            </div>
                            <label for="signature" class="form-label">Upload Signature</label>
                            <input class="form-control" type="file" id="signature" name="signature" accept="image/*">
                        </div>


                        @php
                            $semesters = GENERAL::Semesters();
                            $semesterShort = $semesters[$registration->Semester]
                                ? $semesters[$registration->Semester]['Short']
                                : 'N/A';

                            $schoolYearLabel = GENERAL::setSchoolYearLabel(
                                $registration->SchoolYear,
                                $registration->Semester,
                            );
                        @endphp

                        <div class="mb-0">
                            <label for="school_year" class="form-label">School Year - Semester</label>
                            <input type="text" name="school_year" id="school_year" class="form-control"
                                placeholder="Enter School Year and Semester"
                                value="{{ $schoolYearLabel }} - {{ $semesterShort }}">
                        </div>


                    </div>

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
                            <h4>{{ $registration->Course }}</h4>
                        </div>

                        <div class="mb-4">
                            <label for="specialization" class="form-label">Specialization</label>
                            <h4>{{ $registration->Major }}</h4>
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
                                    placeholder="Enter OR Number" required>
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
                    <button id="processButton" type="submit" class="btn btn-primary mt-2 mb-2"><i
                            class='bx bx-search-alt-2 me-1'></i><span>Print
                            Preview</span></button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" />


    <script>
        let profileImage = document.getElementById('previewProfile');
        let signatureImage = document.getElementById('previewSignature');
        let cropperProfile;
        let cropperSignature;
        let lastCroppedProfileSrc = profileImage.src;
        let lastCroppedSignatureSrc = signatureImage.src;

        document.getElementById('cropProfile').addEventListener('click', function() {
            if (cropperProfile) {
                let croppedCanvas = cropperProfile.getCroppedCanvas();
                profileImage.src = croppedCanvas.toDataURL();
                lastCroppedProfileSrc = profileImage.src;
                croppedCanvas.toBlob(function(blob) {
                    let file = new File([blob], 'profilePicture.png', {
                        type: 'image/png'
                    });
                    let dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    document.getElementById('profilePicture').files = dataTransfer.files;
                });
                cropperProfile.destroy();
                cropperProfile = null;
            } else {
                cropperProfile = new Cropper(profileImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                });
            }
        });

        document.getElementById('cropSignature').addEventListener('click', function() {
            if (cropperSignature) {
                let croppedCanvas = cropperSignature.getCroppedCanvas();
                signatureImage.src = croppedCanvas.toDataURL();
                lastCroppedSignatureSrc = signatureImage.src;
                croppedCanvas.toBlob(function(blob) {
                    let file = new File([blob], 'signature.png', {
                        type: 'image/png'
                    });
                    let dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    document.getElementById('signature').files = dataTransfer.files;
                });
                cropperSignature.destroy();
                cropperSignature = null;
            } else {
                cropperSignature = new Cropper(signatureImage, {
                    aspectRatio: 4 / 1,
                    viewMode: 1,
                });
            }
        });

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

@section('page-script')
    @include('slsu.studentid.js')
@endsection
