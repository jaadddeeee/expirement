@extends('layouts/blankLayout')

@section('title', 'Login Admin')

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')

<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Register -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center">
            <a href="{{url('/')}}" class="app-brand-link gap-2">
              <span class="app-brand-text text-body fw-bolder"><img height = "100" src = "{{asset('images/logo/logo.png')}}"></span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-2 text-center">Welcome to {{config('variables.AppShort')}} Admin!</h4>
          <p class="mb-4">Please sign-in to your account and start the adventure</p>


          @if(!empty(session('autherror')))
              <div class = "alert alert-danger">{{session('autherror')}}</div>
              <?php session(['autherror'=>""])?>
          @endif
          <form id="formAuthentication" class="mb-3">
            @csrf
            <div id="loginmsg"></div>
            <div class="mb-3">
              <label for="email" class="form-label">Campus</label>
              <select class = "form-select" name = "campus" id = "campus">
                <option value="0">Select campus</option>
                @foreach(GENERAL::Campuses() as $index => $campus)
                  <option value="{{Crypt::encryptString($index)}}">{{$campus['Campus']}}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" autofocus>
            </div>
            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>

              </div>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" id = "btnLogin" type="submit">Sign in</button>
            </div>
          </form>


        </div>
      </div>
    </div>
    <!-- /Register -->
  </div>
</div>
</div>
@endsection

{{-- page scripts --}}
@section('page-script')
<script src="{{asset('storage/js/adminlogin.js?id=20240926')}}"></script>
@endsection
