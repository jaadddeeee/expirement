@extends('layouts/contentNavbarLayout')

@section('title', 'What is new to CES')

@section('content')


<div class="row">
  <div class="col-sm-12">

  <div class="card mb-2">

      <div class="card-header d-flex justify-content-between">
          <div class="header-title">
            <h5 class="card-title text-primary">July 20, 2024 updates</h5>
          </div>
        </div>
        <div class="card-body">
            <h5>ADDITIONAL FUNCTIONALITY</h5>
            <ol>
              <li>Department can now generate report on Dean's list</li>
            </ol>
        </div>
      </div>

      <div class="card-header d-flex justify-content-between">
          <div class="header-title">
            <h5 class="card-title text-primary">July 5, 2024 updates</h5>
          </div>
        </div>
        <div class="card-body">
            <h5>ADDITIONAL FUNCTIONALITY</h5>
            <ol>
              <li>UISA office will be the one to encode the requested subject</li>
            </ol>
        </div>
      </div>

      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h5 class="card-title text-primary">April 23, 2024 updates</h5>
        </div>
      </div>
      <div class="card-body">
          <h5>ADDITIONAL FUNCTIONALITY</h5>
          <ol>
            <li>Registrar account can now monitor gradesheet submission.</li>
            <li>Cashier can now edit student fees and scholarship</li>
          </ol>
      </div>
    </div>

    <div class="card mb-2">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h5 class="card-title text-primary">January 19, 2024 updates</h5>
        </div>
      </div>
      <div class="card-body">

          <h5>BUG FIXES</h5>
          <ol>
            <li>Generated Class Record has now backward compatibility</li>
          </ol>
          <h5>ADDITIONAL FUNCTIONALITY</h5>
          <ol>
            <li>Introduces passing percentages of 70%, 60%, 50%</li>
            <li>Additonal module added:</li>
              <ul>
                <li>NSTP office can generate report for serial number generation</li>
                <li>SAS is now able generate good moral certificate programatically</li>
                <li>The registrar is now capable of generating a report on students grade encoded by faculty</li>
              </ul>
          </ol>
      </div>
    </div>

    <div class="card mb-2">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h5 class="card-title text-primary">December 16, 2023 updates</h5>
        </div>
      </div>
      <div class="card-body">

          <h5>ADDED MODULE</h5>
          <ol>
            <li>Clearance is now accessible for the inclusion of obligations.</li>
          </ol>
      </div>
    </div>

    <div class="card mb-2">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h5 class="card-title text-primary">December 13, 2023 updates</h5>
        </div>
      </div>
      <div class="card-body">

          <h5>BUG FIXES</h5>
          <ol>
            <li>Enabling and Logging in using SSO with different name has been fixed</li>
          </ol>
      </div>
    </div>

    <div class="card mb-2">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h5 class="card-title text-primary">November 25, 2023 updates</h5>
        </div>
      </div>
      <div class="card-body">
          <h5>LOGIN MODULE</h5>
          <ol>
            <li>Access CES via Single Sign-On (SSO)</li>
          </ol>
          <h5>BUG FIXES</h5>
          <ol>
            <li>Display room schedules if they are split</li>
            <li>View lists, whether encoded or unencoded, after performing a search in the dashboard</li>
          </ol>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h5 class="card-title text-primary">November 24, 2023 updates</h5>
        </div>
      </div>
      <div class="card-body">
          <h5>TEACHER MODULE</h5>
          <ol>
            <li>Identify and monitor students without assigned grades.</li>
            <li>Track the percentage of encoded subject/s for monitoring purposes.</li>
            <li>Utilize the option to send SMS notifications to the entire class or individual students, displaying SLSU as the sender's name.</li>
            <li>Generate comprehensive class records.</li>
            <li>Generate grade sheets containing the inputted grades.</li>
            <li>Access student information, including details about any disabilities.</li>
          </ol>
          <h5>CLEARANCE MODULE</h5>
          <ol>
            <li>Assign students with outstanding obligations to a specific office</li>
            <li>Easily upload students' data in bulk.</li>
            <li>Delete records once obligations have been settled.</li>
          </ol>
          <h5>ACCOUNT</h5>
          <ol>
            <li>Establish a seamless connection to HRMIS for login convenience.</li>
            <li>Access additional personal information.</li>
            <li>Update the account password securely.</li>
            <li>Utilize a unified platform with access to multiple roles.</li>
          </ol>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
@endsection
