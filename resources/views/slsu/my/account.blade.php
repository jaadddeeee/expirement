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
      <li class="nav-item"><a class="nav-link " href="{{route('my-profile')}}"><i class="bx bx-user me-1"></i> Personal Information</a></li>
      <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="bx bx-bell me-1"></i> Accounts</a></li>

    </ul>
    <div class="card mb-4">
      <h5 class="card-header">Account Details</h5>
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
      @if (empty(auth()->user()->HRMISID))
          <div class="card-body">
            <div class = "alert alert-warning">
              <strong>ENABLE GOOGLE SSO</strong><br>
              To access the platform seamlessly, please connect to HRMIS using Google Single Sign-On (SSO) for streamlined and secure authentication.
              <div class="mt-2">
                  <div id="g_id_onload"
                      data-client_id="{{GENERAL::API()['ClientID']}}"
                      data-callback="onSignIn">
                  </div>
                  <div class="g_id_signin" data-type="standard"></div>
              </div>
            </div>
          </div>
        <hr class="my-0">
      @endif

      <div class="card-body">
          <div class = "row">
              <div class="col-lg-4 col-sm-12">
                <h5 class="fw-semibold">My Current Role/s</h5>
                <div class="demo-inline-spacing mt-3">
                  <ul class="list-group">

                    <li class="list-group-item d-flex justify-content-between align-items-center">Accounting
                      <i class='strong text-danger bx bx-x'></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Clearance
                      <span>
                        @if (ROLE::isClearance())
                          <i class='strong text-success bx bx-check'></i>
                        @else
                          <i class='strong text-danger bx bx-x'></i>
                        @endif
                      </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Cashier
                        <i class='strong text-danger bx bx-x'></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Department
                      <i class='strong text-danger bx bx-x'></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">NSTP
                      <i class='strong text-danger bx bx-x'></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Registrar
                      <i class='strong text-danger bx bx-x'></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Scholarship
                      <i class='strong text-danger bx bx-x'></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      Teacher
                      <span>
                        @if (ROLE::isTeacher())
                          <i class='text-success bx bx-check'></i>
                        @else

                        @endif
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-8 col-sm-12">
                <div class = "row">
                  <h5 class="fw-semibold">My Accounts</h5>
                  <div class="table-responsive text-nowrap">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>System</th>
                            <th>Username</th>
                            <th>password</th>
                            <th>Expires</th>
                          </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                          <tr>
                            <td><strong>CES</strong></td>
                            <td>{{auth()->user()->UserName}}</td>
                            <td>[<span class = "text-primary">encrypted</span>]
                                [<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvaschangepass" aria-controls="offcanvaschangepass">edit</a>]</td>
                            <td>
                              @if (auth()->user()->emp->EmploymentStatus == "Part Timer" or auth()->user()->emp->EmploymentStatus == "Job Order")
                                  {{date('F j, Y', strtotime(auth()->user()->DateEndActive))}}
                              @elseif (auth()->user()->DateEndActive == "2001-01-01")
                                  Never
                              @else
                                  {{date('F j, Y', strtotime(auth()->user()->DateEndActive))}}
                              @endif
                            </td>
                          </tr>
                          <tr>
                            <td><strong>LMS</strong></td>
                            <td>{{auth()->user()->emp->LMSUserName}}</td>
                            <td>{{auth()->user()->emp->LMSPassword}}</td>
                            <td>
                                Never
                            </td>

                          </tr>

                        </tbody>
                      </table>
                    </div>
                </div>
              </div>
          </div>
      </div>
      <!-- /Account -->
    </div>

  </div>
</div>
@endsection

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvaschangepass" aria-labelledby="offcanvasBackdropLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-pencil-square-o"></i> Change Password</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <form id = "frmChangePass">
    <div class="offcanvas-body my-auto mx-0 flex-grow-0">

      <div id = "bulksms"></div>
      <label for="currentpassword">Current Password</label>
      <input type = "password" class = "form-control mb-2" id = "currentpassword" name = "currentpassword" autofocus>

      <label for="newpassword">New Password</label>
      <input type = "password" class = "form-control mb-2" id = "newpassword" name = "newpassword">

      <label for="newretypepassword">Retype New Password</label>
      <input type = "password" class = "form-control mb-2" id = "newretypepassword" name = "newretypepassword">

      <button type="button" id = "btnchangepass" class="btn btn-primary mb-2  w-100"><i class = "fa fa-send-o"></i> Update</button>
      <button type="button" class="btn btn-outline-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>
    </div>
  </form>
</div>

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/account.js?id=20231123')}}"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>

    //  initGoogleSignIn();

    //  function initGoogleSignIn() {
    //     gapi.load('auth2', function() {
    //       gapi.auth2.init({
    //         client_id: '<?=\GENERAL::API()['ClientID']?>',
    //       });
    //     });
    //   }

    //   function signOut() {
    //     var auth2 = gapi.auth2.getAuthInstance();
    //     auth2.signOut().then(function () {
    //       console.log('User signed out.');
    //     });
    //   }

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

        if(user){

          $.ajax({
              url: '/my/connect-info',
              method: 'post',
              data: {email : user.email},
              beforeSend:function(){},
              success:function(response){
                  if (response.Error == 0){
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: response.Message,
                        showConfirmButton: true,
                    })

                    setTimeout(function() {
                      window.location.reload();
                    }, 1000);

                  }else{
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: response.Message,
                        showConfirmButton: true,
                    })
                  }
              }
          });
        }else{
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: "You have no institutional account registered.",
                showConfirmButton: true,
            })
        }
      }
</script>

@endsection
