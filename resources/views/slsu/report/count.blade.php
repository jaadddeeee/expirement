@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
<?php
  function countEnrolled($array, $data){
    $count = 0;
    foreach($array as $r){
      if ($r->Course == $data['Course'] and $r->StudentYear == $data['StudentYear'] and $r->Sex == $data['Sex']){
        $count = $r->countEnrolled;
      }
    }

    return $count;
  }
?>
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
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>

        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}

        </div>
      </div>
      <hr>
      <?php
        $yearlevels = [
          1 => 'First Year',
          2 => 'Second Year',
          3 => 'Third Year',
          4 => 'Fourth Year'
        ];
        $sexs = ['M','F'];
        $gTotal = 0;
        $overall = $counts->sum('countEnrolled');

      ?>
      <div class="card-body">
          <form action = "{{ route('enrolment-count') }}" method = "POST">
            @csrf
            <div class="row g-3 align-items-center">
              @if (auth()->user()->AllowSuper == 1 or ROLE::isVPAA() or ROLE::isPresident())
                <div class="col-auto">
                  <select name="Campus" id="Campus" class="form-select">
                    <option value="0">Select Campus</option>
                      @foreach(GENERAL::Campuses() as $index => $campus)
                        <option value="{{$index}}" <?=$Campus==$index?"Selected":""?>>{{$campus['Campus']}}</option>
                      @endforeach
                  </select>
                </div>
              @endif

              <div class="col-auto">
                <select name="SchoolYear" id="SchoolYear" class="form-select">
                  <option value="0">Select School Year</option>
                    @foreach(GENERAL::SchoolYears() as $sy)
                      <option value="{{$sy}}" <?=($SchoolYear==$sy?"Selected":"")?>>{{$sy."-".($sy+1)}}</option>
                    @endforeach
                </select>
              </div>
              <div class="col-auto">
                <select name="Semester" id="Semester" class="form-select">
                  <option value="0">Select Semester</option>
                    @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value="{{$index}}" <?=($Semester==$index?"Selected":"")?>>{{$sem['Long']}}</option>
                    @endforeach
                </select>
              </div>

              <div class="col-auto">
                <span id="passwordHelpInline" class="form-text">
                  <button class = "btn btn-primary" type = "submit" id = "btnGenerateCount">Generate</button>
                </span>
              </div>

            </div>
          </form>
          <table class="table table-sm table-hover mt-3">
            <thead>
              <tr>
                <th class = 'text-nowrap text-center'>#</th>
                <th class = 'text-nowrap text-center'>Course</th>
                @foreach($yearlevels as $index => $yl)
                <th colspan = 2  class = 'text-nowrap text-center'>{{$yl}}</th>
                @endforeach
                <th colspan = 2  class = 'text-nowrap text-center'>Sub Total</th>
                <th class = 'text-nowrap text-center'>Total</th>
                <th class = 'text-nowrap text-center'>%</th>
              </tr>

              <tr>
                <th></th>
                <th></th>
                @foreach($yearlevels as $index => $yl)
                  @foreach($sexs as $sex)
                    <th class = 'text-nowrap text-center'>{{$sex}}</th>
                  @endforeach
                @endforeach
                <th class = 'text-nowrap text-center'>M</th>
                <th class = 'text-nowrap text-center'>F</th>
                <th class = 'text-nowrap text-center'></th>
              </tr>
            </thead>
            <tbody>
              @foreach($programs as $p)
                <?php
                  $subtotal = 0;
                  $m = 0;
                  $f = 0;
                ?>
                <tr>
                  <td class = 'text-nowrap text-center'>{{isset($ctr)?++$ctr:$ctr=1}}</td>
                  <td class = 'text-nowrap'>{{$p->accro}}</td>
                  @foreach($yearlevels as $index => $yl)
                    @foreach($sexs as $sex)
                      <?php
                          $enrolled = countEnrolled($counts, ['Course' => $p->id, 'StudentYear' => $index, 'Sex' => $sex]);
                          if ($sex == 'M'){
                            $m += $enrolled;
                          }else{
                            $f += $enrolled;
                          }
                          $subtotal += $enrolled;
                          $gTotal +=$enrolled;
                      ?>
                      <td class = 'text-nowrap text-center'>{{$enrolled}}</td>
                    @endforeach
                  @endforeach
                  <td class = 'text-nowrap text-center'>{{$m}}</td>
                  <td class = 'text-nowrap text-center'>{{$f}}</td>
                  <td class = 'text-nowrap text-center'>{{$subtotal}}</td>
                  <td class = 'text-nowrap text-center'>{{(empty($overall)?0:number_format(($subtotal/ $overall)*100,2,'.',','))}}</td>
                </tr>
              @endforeach
              <tr>
                <td colspan = 12 class = "text-nowrap text-end pe-2">GRAND TOTAL</td>
                <td class = 'text-nowrap text-center'>{{$gTotal}}</td>
                <td class = 'text-nowrap text-center'>{{empty($overall)?0:100.00}}</td>
              </tr>
            </tbody>
          </table>

          @if ( $gTotal != $overall)
            <div class="alert alert-danger">
              <strong>MISMATCH</strong><br>
              Enrolled ({{$overall}}) did not matched with Count ({{$gTotal}})
            </div>
          @endif
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')

@endsection
