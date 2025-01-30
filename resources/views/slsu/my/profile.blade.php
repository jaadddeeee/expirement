@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">{{$pageTitle}}</a>
    </li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="bx bx-user me-1"></i> Personal Information</a></li>
      <li class="nav-item"><a class="nav-link" href="{{route('my-account')}}"><i class="bx bx-bell me-1"></i> Accounts</a></li>

    </ul>
    <div class="card mb-4">
      <h5 class="card-header">Profile Details</h5>
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
          <div class="button-wrapper">
            <div style = "font-size: 20px; font-weight: 600">{{strtoupper(auth()->user()->emp->FirstName. ' ' . auth()->user()->emp->LastName)}}</div>
                <small>{{auth()->user()->emp->CurrentItem}}</small><br>
                <small>{{auth()->user()->emp->EmploymentStatus}}</small>
            </div>
        </div>
      </div>
      <hr class="my-0">
      <div class="card-body">
        <form id="formAccountSettings" method="POST" onsubmit="return false">
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="firstName" class="form-label">First Name</label>
              <input class="form-control" type="text" id="firstName" name="firstName" value="{{auth()->user()->emp->FirstName}}" autofocus />
            </div>

            <div class="mb-3 col-md-6">
              <label for="lastName" class="form-label">Middle Name</label>
              <input class="form-control" type="text" name="lastName" id="lastName" value="{{auth()->user()->emp->MiddleName}}" />
            </div>

            <div class="mb-3 col-md-6">
              <label for="lastName" class="form-label">Last Name</label>
              <input class="form-control" type="text" name="lastName" id="lastName" value="{{auth()->user()->emp->LastName}}" />
            </div>
            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="text" id="email" name="email" value="{{auth()->user()->emp->EmailAddress}}" placeholder="" />
            </div>
            <div class="mb-3 col-md-6">
              <label for="organization" class="form-label">Organization</label>
              <input type="text" class="form-control" id="organization" name="organization" value="{{config('variables.SchoolName').' - '.GENERAL::Campuses()[session('campus')]['Campus']}}" />
            </div>
            <div class="mb-3 col-md-6">
              <label class="form-label" for="phoneNumber">Phone Number</label>
              <div class="input-group input-group-merge">
                <input type="text" id="phoneNumber" name="phoneNumber" class="form-control" value="{{auth()->user()->emp->Cellphone}}" />
              </div>
            </div>

          </div>

        </form>
      </div>
      <!-- /Account -->
    </div>

  </div>
</div>
@endsection
