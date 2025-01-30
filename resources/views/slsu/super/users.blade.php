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
  <div class="col-sm-12">
    <div class="card">
      <!-- Notifications -->
      <h5 class="card-header">Set Permissions</h5>
      <div class="card-body">
        <form id = "frmSetPermissions" action = "{{route('all-users')}}" method = "post">
        @csrf
        <div class="row g-3 mb-3 align-items-center">
            @if(auth()->user()->AllowSuper == 1)
            <div class="col-auto">
              <select name="Campus" id="Campus" class="form-select">
                <option value="0">Select Campus</option>
                  @foreach(GENERAL::Campuses() as $index => $campus)
                    <option value="{{$index}}" <?=($index==$Campus?"Selected":"")?>>{{$campus['Campus']}}</option>
                  @endforeach
              </select>
            </div>
            @endif
            <div class="col-auto">
              <select name="Employee" id="Employee" class="form-select">
                <option value="0">Select Employee</option>
                  @foreach($employees as $employee)
                    <option value="{{$employee->id}}" <?=($employee->id==$Employee?"Selected":"")?>>{{$employee->LastName . ", " . $employee->FirstName}}</option>
                  @endforeach
              </select>
            </div>

            <div class="col-auto">
              <span id="passwordHelpInline" class="form-text">
                <button class = "btn btn-primary" type = "submit" id = "btnViewClassess">View</button>
              </span>
            </div>
        </div>
        </form>
        @if(!empty($Employee))
          <span>Select the checkbox to grant the employee the desired permission.</span></span>
          @if (!empty(session('ErrorPermission')))
              <div class = "alert alert-danger mt-2">{{session('ErrorPermission')}}</div>
              <?php
                session(['ErrorPermission' => ""]);
              ?>
          @endif
          <form action = "{{route('save-permission')}}" method = "post">
            @csrf
          <input type = "text" name = "Campus" value = "{{$Campus}}" hidden>
          <input type = "text" name = "hiddenID" value = "{{$Employee}}" hidden>
          <div class="table-responsive">
            <table class="table table-hover table-striped table-borderless">
              <thead>
                <tr>
                  <th class="text-nowrap">Type</th>
                  <th class="text-nowrap text-center">Allowed</th>
                  <th class="text-nowrap text-center">Sub Type</th>
                  <th class="text-nowrap text-center">For Clearance (Department)</th>
                </tr>
              </thead>
              <tbody>
                @foreach($permissions as $permission)
                <tr>
                  <td class="text-nowrap"><div for = 'allowed-{{$permission->id}}'>{{$permission->DisplayName}}</div></td>
                  <td>
                    <div class="form-check d-flex justify-content-center">
                      <?php
                          $hasRole = ROLE::hasRole($permission->PermissionType, $Employee, $Campus);
                      ?>
                      <input name = "setPer[]" {{(!empty($hasRole)?"checked":"")}} class="form-check-input" value = "{{$permission->PermissionType}}" type="checkbox" id="allowed-{{$permission->id}}" />
                    </div>
                  </td>
                  <td>
                        @if(strtolower($permission->PermissionType) == "clearance")
                          <select name="ClearanceType" id="ClearanceType" class="form-select">
                            <option value="0">Select Sub Type for Clearance</option>
                            @foreach($subType as $st)
                            <option <?=(isset($hasRole->ClearanceRole)?$hasRole->ClearanceRole==$st->Description?"Selected":"":"")?>>{{$st->Description}}</option>
                            @endforeach
                          </select>
                        @elseif(strtolower($permission->PermissionType) == "department")
                          <select name="Department" id="Department" class="form-select">
                            <option value="0">Select Department</option>
                          @foreach($departments as $dept)
                            <option value = "{{$dept->id}}" <?=(isset($hasRole->DepartmentID)?$dept->id==$hasRole->DepartmentID?"Selected":"":"")?>>{{$dept->DepartmentName}}</option>
                          @endforeach
                          </select>
                        @endif
                  </td>
                  <td>

                      @if(strtolower($permission->PermissionType) == "clearance")
                        <select name="Department2" id="Department2" class="form-select">
                          <option value="0">Select Department</option>
                          @foreach($departments as $dept)
                            <option value = "{{$dept->id}}" <?=(isset($hasRole->DepartmentID)?$dept->id==$hasRole->DepartmentID?"Selected":"":"")?>>{{$dept->DepartmentName}}</option>
                          @endforeach
                        </select>
                      @endif

                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Save changes</button>

            <?php
              $hasRoleDept = ROLE::hasRole("department", $Employee, $Campus);
            ?>

            @if (!empty($hasRoleDept))
                <a href = "#" class="btn btn-danger ms-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAssignedPrograms" aria-controls="offcanvasBackdrop">Assign program</a>
            @endif

          </div>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAssignedPrograms" aria-labelledby="offcanvasBackdropLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-user"></i> ASSIGN PROGRAM</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <form id = "frmAssignedProgram">

    <input hidden type="text" id="hidCampus" name = "hidCampus" value = "{{$Campus}}">
    <input hidden type="text" id="hidEmployeeID" name = "hidEmployeeID" value = "{{$Employee}}">
    <div class="offcanvas-body my-auto mx-0 flex-grow-0">
      <div id="assignedprogrammsg"></div>
      <label for="newprogram" class="form-label">Select program</label>
      <select name="newprogram" id="newprogram" class = "form-select mb-3">
        <option value = ""></option>
        @foreach($courses as $c)
        <option value = "{{$c->id}}">{{$c->accro}}</option>
        @endforeach
      </select>
      <button type="submit" id = "btnAssignProgram" class="btn btn-primary mb-2 w-100"><i class = "fa fa-user"></i> Assign Now</button>
      <button type="button" class="btn btn-outline-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>

      <div class = "divider">
          <div class="divider-text">Assigned Programs</div>
      </div>

      <table class="table-sm table-hover">
        <thead>
          <tr>
            <th>Action</th>
            <th>Program</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assignedprograms as $ap)
          <tr>
            <td><i class = "fa fa-trash text-danger"></i></td>
            <td>{{$ap->accro}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </form>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/superadmin.js?id=20240516')}}"></script>
@include('slsu.super.js')
@endsection
