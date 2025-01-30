@extends('layouts/blankLayout')

@section('title', 'Login')

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
          <h4 class="mb-2 text-center">Welcome to {{config('variables.AppShort')}}!</h4>
          <p class="mb-4">Please sign-in to your account and start the adventure</p>
          <div class="mt-2 text-center">
              <div id="g_id_onload"
                  data-client_id="{{GENERAL::API()['ClientID']}}"
                  data-callback="onSignIn">
              </div>
              <div class="g_id_signin form-control" data-type="standard"></div>
          </div>
          <div class="divider">
              <div class="divider-text text-uppercase text-muted"><b> or Enter your account below </b></div>
          </div>
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
<script src="{{asset('storage/js/guestlogin.js?id=20230825')}}"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>

      function onFailure(error) {
        $("#loginmsg").html(error);
      }

      function decodeJwtResponse(token) {
          let base64Url = token.split('.')[1]
          let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
          let jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
              return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
          }).join(''));
          return JSON.parse(jsonPayload)
      }

      window.onSignIn = googleUser => {

        var user = decodeJwtResponse(googleUser.credential);
        // dd(user);
        if(user){
          // signOut();
          $.ajax({
              url: '/auth/sso',
              method: 'post',
              data: {email : user.email},
              beforeSend:function(){},
              success:function(response){
                  console.log(response);
                  if (response.status_code == 0){
                      $("#loginmsg").html("<div class = 'alert alert-success'>Redirecting, please wait...</div>");
                      window.location.href = "/";
                  }else{
                    $("#loginmsg").html(response.Message);
                  }
              }
          });
        }else{
          $("#loginmsg").html("<div class = 'alert alert-danger'>You have no institutional account registered.</div>");
        }
      }
</script>


@endsection
