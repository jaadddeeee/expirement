@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');

@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif


      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="bx bx-menu bx-sm"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
          {{(GENERAL::isMobile()?config('variables.AppShort'):config('variables.AppName'))}}
        </div>
        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">

          <!-- Place this tag where you want the button to render. -->
          @if (auth()->check())
                @if (auth()->user()->AllowSuper == 1 or ROLE::isRegistrar() or ROLE::isDepartment())
                  <li class="nav-item lh-1 me-2">
                  <a class="nav-link nav-link-label" href="#" data-bs-toggle="modal" data-bs-target = "#modalSearch"><i class="bx bx-search fs-4 lh-0"></i></a>
                  </li>
                @endif
          @endif
          @if (auth()->user()->AllowSuper == 1)
          <li class="nav-item lh-1 navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" >
              <i class="fs-4 lh-0 ficon bx bx-cog bx-flip-horizontal"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">

                    <div class="flex-grow-1">
                      <span class="fw-semibold d-block">
                        Faculty Evaluation Setup
                      </span>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('faculty-evaluation-index')}}">
                  <i class="bx bx-calendar me-2"></i>
                  <span class="align-middle">Schedule</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('faculty-evaluation-export')}}">
                  <i class="bx bx-export me-2"></i>
                  <span class="align-middle">Export Result</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('faculty-evaluation-export-pdf')}}">
                  <i class="bx bxs-file-pdf me-2"></i>
                  <span class="align-middle">Export to PDF</span>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">

                    <div class="flex-grow-1">
                      <span class="fw-semibold d-block">
                        Update Employee Info
                      </span>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('update-all-info')}}">
                  <i class="bx bx-edit me-2"></i>
                  <span class="align-middle">Import from HRMIS</span>
                </a>
              </li>
            </ul>
          </li>
          @endif
          @if (auth()->user()->AllowSuper == 1 or ROLE::isDepartmentHead())
          <li class="nav-item lh-1 navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" >
              <i class="fs-4 lh-0 ficon bx bx-file bx-flip-horizontal"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">

                    <div class="flex-grow-1">
                      <span class="fw-semibold d-block">
                        Special Reports
                      </span>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('faculty-evaluation-all')}}">
                  <i class="bx bx-user me-2"></i>
                  <span class="align-middle">Faculty Evaluation</span>
                </a>
              </li>
            </ul>
          </li>
          @endif
          @if (auth()->user()->AllowSuper == 1 or strtolower(auth()->user()->AccountLevel) == "administrator")
          <li class="nav-item lh-1 me-3 navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" >
              <i class="fs-4 lh-0 ficon bx bx-user-plus bx-flip-horizontal"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">

                    <div class="flex-grow-1">
                      <span class="fw-semibold d-block">
                        Add User Accounts
                      </span>
                      <small class="text-muted">
                          Student | Employee
                      </small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('clearance.student')}}">
                  <i class="bx bx-user me-2"></i>
                  <span class="align-middle">Student Clearance</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('admin-users')}}">
                  <i class="bx bx-key me-2"></i>
                  <span class="align-middle">CES</span>
                </a>
              </li>
            </ul>
          </li>
          @endif
          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt class="w-px-40 h-auto rounded-circle">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online">
                        <img src="{{(empty(auth()->user()->emp->profilephoto)?asset('images/logo/logo.png'):auth()->user()->emp->profilephoto)}}" alt class="w-px-40 h-auto rounded-circle">
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <span class="fw-semibold d-block">
                        @if(auth()->check())
                            {{auth()->user()->emp->FirstName. ' '. auth()->user()->emp->LastName}}
                        @else
                        @endif
                      </span>
                      <small class="text-muted">
                          @if (auth()->check())
                            @if (auth()->user()->AllowSuper == 1)
                                Super Admin
                            @else
                                @if (count(Auth::user()->role) > 1)
                                    Multi Role
                                @elseif (count(Auth::user()->role) == 0)
                                    Guest
                                @else
                                    {{Auth::user()->role[0]->Role}}
                                @endif
                            @endif
                          @else
                            Invalid
                          @endif
                      </small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('my-profile')}}">
                  <i class="bx bx-user me-2"></i>
                  <span class="align-middle">My Profile</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('my-account')}}">
                  <i class="bx bx-key me-2"></i>
                  <span class="align-middle">My Account</span>
                </a>
              </li>
              @if (auth()->check())
                @if (auth()->user()->AllowSuper == 1)
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{route('all-default-values')}}">
                      <i class="bx bx-cog me-2"></i>
                      <span class="align-middle">Preferences</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{route('all-clearance')}}">
                      <i class="bx bx-cog me-2"></i>
                      <span class="align-middle">Clearance</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{route('all-users')}}">
                      <i class="bx bx-group me-2"></i>
                      <span class="align-middle">User Accounts</span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{route('all-surveys')}}">
                      <i class='bx bx-conversation me-2'></i>
                      <span class="align-middle">Survey</span>
                    </a>

                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{route('accounts-receivable')}}">
                      <i class='bx bx-money me-2'></i>
                      <span class="align-middle">Accounts Receivable</span>
                    </a>
                  </li>

                  <li>
                    <a class="dropdown-item" href="{{route('tuition-payments')}}">
                      <i class='bx bx-money me-2'></i>
                      <span class="align-middle">Payments from Tuition</span>
                    </a>
                  </li>

                @endif
              @endif

              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('logout')}}">
                  <i class='bx bx-power-off me-2'></i>
                  <span class="align-middle">Log Out</span>
                </a>
              </li>
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->
