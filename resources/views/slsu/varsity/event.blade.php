@extends('layouts/contentNavbarLayout')

@section('title', config('variables.AppName'))

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection



@section('content')
    <div class="col-lg-12 col-md-12 mb-4">
      <div class="card">
        <div class="row">
          <div class="col-sm-12">
            <div class="card-body">
              <h5 class="card-title text-primary">Event Management</h5>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr>
                    <th class = "text-nowrap">PROGRAM</th>
                    <th class = "text-nowrap text-center">FOR ENROLLMENT</th>
                    <th class = "text-nowrap text-center">FOR FHE</th>
                    <th class = "text-nowrap text-center">FOR ASSESSMENT</th>
                    <th class = "text-nowrap text-center">FOR VALIDATION</th>
                    <th class = "text-nowrap text-center">VALIDATED</th>
                    <th class = "text-nowrap text-center">TOTAL</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

@endsection

@section('page-script')
<script src="{{asset('storage/js/dashboards-analytics.js?id=20240425b')}}"></script>
@endsection
