<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$controller_path = 'App\Http\Controllers';

Route::get('linkstorage', function(){
  Artisan::call('storage:link');
});



//super admin

Route::group(['prefix' => 'super-admin','middleware' => ['super']], function () use ($controller_path){
  Route::get('/preferences', $controller_path . '\SLSU\Super\SuperAdminController@preferences')->name('all-default-values');
  Route::get('/clearance', $controller_path . '\SLSU\Super\SuperAdminController@clearance')->name('all-clearance');
  Route::get('/users', $controller_path . '\SLSU\Super\SuperAdminController@users')->name('all-users');
  Route::post('/users', $controller_path . '\SLSU\Super\SuperAdminController@users')->name('all-users');
  Route::post('/assess-clearance', $controller_path . '\SLSU\Super\SuperAdminController@proclearance');
  Route::post('/view-preferences', $controller_path . '\SLSU\Super\SuperAdminController@view');
  Route::post('/save-preferences', $controller_path . '\SLSU\Super\SuperAdminController@save');
  Route::post('/employee-campus', $controller_path . '\SLSU\Super\SuperAdminController@employeecampus');
  Route::post('/save-permission', $controller_path . '\SLSU\Super\SuperAdminController@savepermission')->name('save-permission');
  //Search global

  Route::get('/view-one', $controller_path . '\SLSU\Super\GlobalSearchController@globalone')->name('admin-one-student');
  Route::post('/assigned-program', $controller_path . '\SLSU\Super\SuperAdminController@assignedprogram');

  Route::get('/update-from-hrmis', $controller_path . '\SLSU\Super\SuperAdminController@importhrmis')->name('update-all-info');
  Route::post('/pro-import-hrmis', $controller_path . '\SLSU\Super\SuperAdminController@proimporthrmis');

});

//Admin

Route::group(['prefix' => 'admin','middleware' => ['admin']], function () use ($controller_path){
  Route::get('/users', $controller_path . '\SLSU\Super\SuperAdminController@users')->name('admin-users');
  Route::post('/users', $controller_path . '\SLSU\Super\SuperAdminController@users')->name('all-users');
  Route::post('/save-permission', $controller_path . '\SLSU\Super\SuperAdminController@savepermission')->name('save-permission');
});

//search
Route::group(['prefix' => 'search','middleware' => ['auth']], function () use ($controller_path){
  Route::post('/or', $controller_path . '\SLSU\SearchController@or');

  Route::get('/global-search', $controller_path . '\SLSU\Super\GlobalSearchController@startsearch');
  Route::post('/global-res-search', $controller_path . '\SLSU\Super\GlobalSearchController@globalsearch');

  //GRADES
  Route::post('/view-grades', $controller_path . '\SLSU\Super\GlobalSearchController@grades');
});

// Main Page Route
Route::get('/', $controller_path . '\SLSU\HomeController@index')->name('home')->middleware('auth');

Route::post('/sem_year_analytics', $controller_path . '\SLSU\HomeController@sem_year_analytics')->name('sem_year_analytics')->middleware('auth');
Route::post('/sem_year_gs', $controller_path . '\SLSU\HomeController@sem_year_gs')->middleware('auth');

//whatsnew


Route::get('/whatsnew', $controller_path . '\SLSU\WhatsnewController@index')->name('whatsnew');
// my

Route::group(['prefix' => 'my','middleware' => ['auth']], function () use ($controller_path){
  Route::get('/profile', $controller_path . '\SLSU\ProfileController@index')->name('my-profile');
  Route::get('/account', $controller_path . '\SLSU\ProfileController@account')->name('my-account');
  Route::post('/connect-info', $controller_path . '\SLSU\ProfileController@update')->name('update-account');
  Route::post('/update-account', $controller_path . '\SLSU\AccountController@update')->name('update-account');
});

Route::group(['prefix' => 'typeahead','middleware' => ['auth']], function () use ($controller_path){
  Route::get('/search', $controller_path . '\SLSU\TypeAheadController@search');
  Route::get('/fees', $controller_path . '\SLSU\TypeAheadController@fees');
  Route::get('/sch', $controller_path . '\SLSU\TypeAheadController@sch');
  Route::get('/emp', $controller_path . '\SLSU\TypeAheadController@emps');
});

// authentication
Route::group(['prefix' => 'all', 'middleware' => ['auth']], function () use ($controller_path){
    Route::post('department-campus', $controller_path . '\SLSU\DepartmentController@list');
});

Route::group(['prefix' => 'auth'], function () use ($controller_path){
  Route::get('/login-basic', $controller_path . '\authentications\LoginBasic@index')->name('login');
  Route::get('/login-basic-admin', $controller_path . '\authentications\LoginBasic@indexadmin');
  Route::get('/logout', $controller_path . '\authentications\LoginBasic@destroy')->name('logout');
  Route::post('/login-attempt', $controller_path . '\authentications\LoginBasic@login')->name('attempt-login');
  Route::post('/login-attempt-admin', $controller_path . '\authentications\LoginBasic@admin');
  Route::post('/sso', $controller_path . '\authentications\LoginBasic@sso')->name('attempt-login');
});

//Scholarship
Route::group(['prefix' => 'scholarship', 'middleware' => ['auth','scholarship']], function () use ($controller_path){
  Route::get('/set-up/type', $controller_path . '\SLSU\ScholarshipNewController@type');
});

//TES
Route::group(['prefix' => 'tes', 'middleware' => ['auth','tes']], function () use ($controller_path){
  Route::get('/', $controller_path . '\SLSU\TESController@index');
  Route::post('/search', $controller_path . '\SLSU\TESController@search');
  Route::get('/search', $controller_path . '\SLSU\TESController@search')->name('one-tes');
  Route::post('mark-fhe', $controller_path . '\SLSU\TESController@markfhe');
  Route::post('mark-nonfhe', $controller_path . '\SLSU\TESController@marknonfhe');
  Route::post('reset-fhe', $controller_path . '\SLSU\TESController@resetfhe');
});

//NSTP
Route::group(['prefix' => 'nstp', 'middleware' => ['auth','nstp']], function () use ($controller_path){
  Route::get('/with-serial', $controller_path . '\SLSU\NSTPController@withserial')->name('with-serial');
  Route::get('/with-no-serial', $controller_path . '\SLSU\NSTPController@withnoserial')->name('with-no-serial');
  Route::post('/search-no-serial', $controller_path . '\SLSU\NSTPController@searchnoserial')->name('searchnoserial');
  Route::get('/grades', $controller_path . '\SLSU\NSTPController@grade')->name('nstp-grade');
  Route::post('/studentsearch', $controller_path . '\SLSU\NSTPController@studentsearch');
  Route::get('/officially-enrolled', $controller_path . '\SLSU\NSTPController@masterlist');
  Route::post('/officially-enrolled', $controller_path . '\SLSU\NSTPController@masterlistpro');
});

//CHED Report
Route::group(['prefix' => 'ched/report', 'middleware' => ['auth','registrar']], function () use ($controller_path){
  Route::get('/graduation-list', $controller_path . '\SLSU\CHEDReportController@graduationlist');
  Route::post('graduation-list-dates', $controller_path . '\SLSU\CHEDReportController@graduationdatelist');
  Route::post('/graduation-list', $controller_path . '\SLSU\CHEDReportController@graduationlist');
  Route::post('/generate-graduation-list', $controller_path . '\SLSU\CHEDReportController@pdf')->name('grad.pdf');
});

//Registrar
Route::group(['prefix' => 'registrar', 'middleware' => ['auth','registrar']], function () use ($controller_path){

  Route::group(['prefix' => 'report'], function () use ($controller_path){
    Route::get('/arta', $controller_path . '\SLSU\RegistrarController@arta')->name('arta');
    Route::get('/unencoded', $controller_path . '\SLSU\RegistrarController@unencoded')->name('unencoded');
    Route::post('/pro-unencoded', $controller_path . '\SLSU\RegistrarController@prounencoded');
    Route::get('/formslkra', $controller_path . '\SLSU\RegistrarController@formslkra');
    Route::post('/pro-frmfte', $controller_path . '\SLSU\RegistrarController@proformslkra');
    Route::post('/pro-arta', $controller_path . '\SLSU\RegistrarController@proarta');
  });

  Route::group(['prefix' => 'pdf'], function () use ($controller_path){
    Route::get('/generate-orf-assessment', $controller_path . '\SLSU\Report\ReportController@orfassessment')->name('generate-orf-assessment');
    Route::get('/generate-route-slip', $controller_path . '\SLSU\Report\ReportController@routeslip')->name('generate-route-slip');
    Route::get('/generate-data-privacy', $controller_path . '\SLSU\Report\ReportController@dataprivacy')->name('generate-data-privacy');
    Route::get('/enrolment-form', $controller_path . '\SLSU\Report\ReportController@enrolmentform')->name('enrolment-form');

  });

    //Enrolment
    Route::get('/enrolment', $controller_path . '\SLSU\EnrolmentController@regenrolment')->name('step4list');
    Route::post('/enrolment-list', $controller_path . '\SLSU\EnrolmentController@regenrolmentlist')->name('step4list-pro');
    // Route::get('/validate', $controller_path . '\SLSU\EnrolmentController@validateenrolment')->name('validate');
    Route::post('/validate-pro', $controller_path . '\SLSU\EnrolmentController@validatepro');
    Route::post('/withdraw', $controller_path . '\SLSU\EnrolmentController@withdraw');


    Route::group(['prefix' => 'certificate'], function () use ($controller_path){
    Route::get('/', $controller_path . '\SLSU\RegistrarController@certificates')->name('certificates');
  });

  Route::group(['prefix' => 'tracker'], function () use ($controller_path){
    Route::get('/gradesheet', $controller_path . '\SLSU\TrackerController@listgradesheet');
    Route::post('/search-gradesheet', $controller_path . '\SLSU\TrackerController@searchgradesheet');
    Route::post('/accept-gradesheet', $controller_path . '\SLSU\TrackerController@acceptgradesheet');
    Route::get('/list-no-submit-gradesheets', $controller_path . '\SLSU\TrackerController@nosubmitgradesheet')->name('list-no-submit-gradesheets');
  });

});

//UISA
Route::group(['prefix' => 'uisa', 'middleware' => ['auth','uisa']], function () use ($controller_path){
  Route::get('/requested-subject', $controller_path . '\SLSU\UISAController@requestedsubject')->name('requestedsubject');
  Route::post('/search-cc',$controller_path . '\SLSU\UISAController@prorequestedsubject');
  Route::post('/search-student',$controller_path . '\SLSU\UISAController@searchstudent');
  Route::post('/requested-save',$controller_path . '\SLSU\UISAController@saverequestedsubject');
  Route::post('/requested-delete',$controller_path . '\SLSU\UISAController@deleterequestedsubject');
});

//ENROLMENT
Route::group(['prefix' => 'enrol', 'middleware' => ['auth','enrol']], function () use ($controller_path){
  Route::post('/save', $controller_path . '\SLSU\EnrolmentController@save');
  Route::post('/student-status', $controller_path . '\SLSU\EnrolmentController@studentstatus');
  Route::post('/cart', $controller_path . '\SLSU\EnrolmentController@cart');
  Route::post('/delete-cart', $controller_path . '\SLSU\EnrolmentController@deletecart');
  Route::post('/finalize', $controller_path . '\SLSU\EnrolmentController@finalize');
  Route::post('/student-manual-enrol', $controller_path . '\SLSU\EnrolmentController@manualenrol');

  //Adding//Change//Dropping
  Route::post('/search-enrollee', $controller_path . '\SLSU\EnrolmentController@searchenrollee')->name('search-enrollee');
  Route::get('/validate', $controller_path . '\SLSU\EnrolmentController@validateenrolment')->name('validate');
  Route::post('/addschedule-manual', $controller_path . '\SLSU\EnrolmentController@popuschedule');
  Route::post('/pro-add-subject', $controller_path . '\SLSU\EnrolmentController@proaddsubject');
  Route::post('/pro-drop-subject', $controller_path . '\SLSU\EnrolmentController@prodropsubject');
  Route::post('/pro-modify-subject', $controller_path . '\SLSU\EnrolmentController@promodifysubject');

});

//Department
Route::group(['prefix' => 'department', 'middleware' => ['auth','department']], function () use ($controller_path){

  Route::group(['prefix' => 'report'], function () use ($controller_path){
    Route::get('/pre-reg-survey', $controller_path . '\SLSU\DepartmentController@preregsurvey');
  });

  Route::group(['prefix' => 'certificate'], function () use ($controller_path){
    Route::get('/', $controller_path . '\SLSU\RegistrarController@certificates')->name('certificates');
  });

  Route::group(['prefix' => 'employee'], function () use ($controller_path){
    Route::get('/', $controller_path . '\SLSU\DepartmentController@employee');
    Route::post('/delete', $controller_path . '\SLSU\DepartmentController@destroyemp');
    Route::get('/edit/{id}', $controller_path . '\SLSU\DepartmentController@edit');
  });

  Route::group(['prefix' => 'student'], function () use ($controller_path){
    Route::get('/one', $controller_path . '\SLSU\StudentController@onestudent')->name('view-one-student');
  });


  //Tracker
  Route::get('/track', $controller_path . '\SLSU\DepartmentController@enrolmenttracker')->name('track-enrolment');
  Route::post('/track', $controller_path . '\SLSU\DepartmentController@enrolmenttracker');

  //survey
  Route::post('/pro-prereg-search', $controller_path . '\SLSU\DepartmentController@propreregsearch');
  Route::post('/pro-prereg-search-list', $controller_path . '\SLSU\DepartmentController@propreregsearchlist');

  Route::get('/enrolment', $controller_path . '\SLSU\DepartmentController@enrolment')->name('step1lists');
  Route::post('/enrolment', $controller_path . '\SLSU\DepartmentController@enrolment');
  Route::get('/view-enrolment', $controller_path . '\SLSU\DepartmentController@proenrolment')->name('pro-enrolment');


});


//Survey Super
Route::group(['prefix' => 'surveys', 'middleware' => ['auth','super']], function () use ($controller_path){
  Route::get('/', $controller_path . '\SLSU\SurveyController@index')->name('all-surveys');
  Route::post('/', $controller_path . '\SLSU\SurveyController@dataonly');
  Route::post('/save', $controller_path . '\SLSU\SurveyController@store');
  Route::get('/one', $controller_path . '\SLSU\SurveyController@show')->name('one-survey');
  Route::get('/results', $controller_path . '\SLSU\SurveyController@results')->name('results-survey');

  Route::post('/saveq', $controller_path . '\SLSU\SurveyController@storequestion');

});

//Faculty Evaluation Super
Route::group(['prefix' => 'faculty-evaluation', 'middleware' => ['auth']], function () use ($controller_path){

  Route::group(['middleware' => ['super']], function () use ($controller_path){
    Route::get('/', $controller_path . '\SLSU\FacultyEvaluationController@index')->name('faculty-evaluation-index');
    Route::get('/one', $controller_path . '\SLSU\FacultyEvaluationController@one')->name('faculty-evaluation-one');
    Route::get('/analytics', $controller_path . '\SLSU\FacultyEvaluationController@analytics')->name('faculty-evaluation-analytics');
    Route::get('/export', $controller_path . '\SLSU\FacultyEvaluationController@export')->name('faculty-evaluation-export');
    Route::get('/export-pdf', $controller_path . '\SLSU\FacultyEvaluationController@exportpdf')->name('faculty-evaluation-export-pdf');
    Route::get('/pro-export', $controller_path . '\SLSU\FacultyEvaluationController@proexport')->name('afes-pro-export');
    Route::get('/pro-export-pdf', $controller_path . '\SLSU\Report\ReportController@afespdf')->name('afes-pro-export-pdf');
  });

  Route::group(['middleware' => ['afes']], function () use ($controller_path){
    Route::get('/all', $controller_path . '\SLSU\FacultyEvaluationController@allresults')->name('faculty-evaluation-all');
    Route::get('/all-pro', $controller_path . '\SLSU\FacultyEvaluationController@allresults')->name('faculty-evaluation-all');
    Route::post('/all-pro', $controller_path . '\SLSU\FacultyEvaluationController@allresults')->name('pro-all-evaluation');
    Route::get('/indexvpaa', $controller_path . '\SLSU\FacultyEvaluationController@indexvpaa')->name('indexvpaa');
  });
});

//AR Super

Route::group(['prefix' => 'accounts-receivable', 'middleware' => ['auth','super']], function () use ($controller_path){
  Route::get('/', $controller_path . '\SLSU\ARController@index')->name('accounts-receivable');
  Route::post('/generate', $controller_path . '\SLSU\ARController@generate');
  Route::get('/tuition-fee', $controller_path . '\SLSU\ARController@paymenttuition')->name('tuition-payments');
  Route::post('/generate-tuition', $controller_path . '\SLSU\ARController@generatetuition');
});


Route::group(['prefix' => 'recognition', 'middleware' => ['auth','department']], function () use ($controller_path){
  Route::get('/latin-honors', $controller_path . '\SLSU\HonorController@latinhonors');
  Route::post('/pro-latin', $controller_path . '\SLSU\HonorController@prolatinhonors');

  Route::get('/deans-list', $controller_path . '\SLSU\HonorController@deanslist');
  Route::post('/pro-dean-list', $controller_path . '\SLSU\HonorController@prodeanslist');

});

//OSAS
Route::group(['prefix' => 'osas', 'middleware' => ['auth','osas']], function () use ($controller_path){
  Route::group(['prefix' => 'certificate'], function () use ($controller_path){
    Route::get('/', $controller_path . '\SLSU\OSASController@certificates')->name('certificates');
  });
});

//teacher
Route::group(['prefix' => 'teacher', 'middleware' => ['auth','teacher']], function () use ($controller_path){
  Route::get('/my-class', $controller_path . '\SLSU\TeacherController@index')->name('my-class');
  Route::get('/one-student', $controller_path . '\SLSU\TeacherController@onestudent')->name('onestudent');
  Route::post('/view-my-class', $controller_path . '\SLSU\TeacherController@view')->name('view-my-class');
  Route::get('/list-students', $controller_path . '\SLSU\TeacherController@students')->name('list-students');

  Route::get('/encode-grades', $controller_path . '\SLSU\TeacherController@grades')->name('encode-grades');
  Route::post('/view-encode-grades', $controller_path . '\SLSU\TeacherController@viewgrades')->name('view-encode-grades');
  Route::get('/list-grades', $controller_path . '\SLSU\TeacherController@listgrades')->name('list-grades');

  Route::get('/unencoded', $controller_path . '\SLSU\TeacherController@unencoded')->name('view.unencoded');
  Route::get('/encoded', $controller_path . '\SLSU\TeacherController@encoded')->name('view.encoded');

  Route::get('/faculty-evaluation', $controller_path . '\SLSU\TeacherController@facultyevaluation')->name('faculty-evaluation');
});

//clearance accounts
Route::group(['prefix' => 'clearance', 'middleware' => ['auth','admin']], function () use ($controller_path){
  Route::get('/student', $controller_path . '\SLSU\ClearanceController@student')->name('clearance.student');
  Route::post('/student-save', $controller_path . '\SLSU\ClearanceController@studentsave');
  Route::post('/student-delete', $controller_path . '\SLSU\ClearanceController@studentdelete');
});

//clearance department
Route::group(['prefix' => 'clearance', 'middleware' => ['auth','clearance']], function () use ($controller_path){
  Route::get('/', $controller_path . '\SLSU\ClearanceController@index')->name('manage');
  Route::get('/autocomplete', $controller_path . '\SLSU\ClearanceController@search')->name('autocomplete');

  Route::post("/addDepartment", $controller_path . '\SLSU\ClearanceController@adddepartment')->name('adddepartment');
  Route::post("/removeDepartment", $controller_path . '\SLSU\ClearanceController@removeDepartment')->name('removeDepartment');

  Route::post("/uploaddepartment", $controller_path . '\SLSU\ClearanceController@uploaddepartment')->name('uploaddepartment');

  Route::post("/studentsearch", $controller_path . '\SLSU\ClearanceController@studentsearch')->name('studentsearch');
});

//cashier
Route::group(['prefix' => 'cashier', 'middleware' => ['auth','cashier']], function () use ($controller_path){
    Route::group(['prefix' => 'set-up'], function () use ($controller_path){
        Route::get('/scholarships', $controller_path . '\SLSU\ScholarshipController@index')->name('scholarships');
        Route::post('/save-scholarship', $controller_path . '\SLSU\ScholarshipController@save');
    });

    Route::get('/payment', $controller_path . '\SLSU\CashierController@index');
    Route::post('/search-payment', $controller_path . '\SLSU\CashierController@searchpayment');
    Route::post('/search-general-fees', $controller_path . '\SLSU\CashierController@searchgenfee');
    Route::post('/add-fee', $controller_path . '\SLSU\CashierController@savefee');
    Route::post('/delete-fee', $controller_path . '\SLSU\CashierController@deletefee');
    Route::post('/get-record', $controller_path . '\SLSU\CashierController@getrecord');
    Route::post('/update-scholarship', $controller_path . '\SLSU\CashierController@updatescholar');

});


//clearance library
Route::group(['prefix' => 'library', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\LibraryController@search');

  Route::post("/add", $controller_path . '\SLSU\LibraryController@add');
  Route::post("/remove", $controller_path . '\SLSU\LibraryController@remove');

  Route::post("/upload", $controller_path . '\SLSU\LibraryController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\LibraryController@studentsearch');
});

//clearance DORM Boy
Route::group(['prefix' => 'dormb', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\DORMBController@search');

  Route::post("/add", $controller_path . '\SLSU\DORMBController@add');
  Route::post("/remove", $controller_path . '\SLSU\DORMBController@remove');

  Route::post("/upload", $controller_path . '\SLSU\DORMBController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\DORMBController@studentsearch');
});

//clearance DORM Girl
Route::group(['prefix' => 'dormg', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\DORMGController@search');

  Route::post("/add", $controller_path . '\SLSU\DORMGController@add');
  Route::post("/remove", $controller_path . '\SLSU\DORMGController@remove');

  Route::post("/upload", $controller_path . '\SLSU\DORMGController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\DORMGController@studentsearch');
});

//clearance Guidance
Route::group(['prefix' => 'guidance', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\GuidanceController@search');

  Route::post("/add", $controller_path . '\SLSU\GuidanceController@add');
  Route::post("/remove", $controller_path . '\SLSU\GuidanceController@remove');

  Route::post("/upload", $controller_path . '\SLSU\GuidanceController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\GuidanceController@studentsearch');
});

//clearance Clinic
Route::group(['prefix' => 'clinic', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\ClinicController@search');

  Route::post("/add", $controller_path . '\SLSU\ClinicController@add');
  Route::post("/remove", $controller_path . '\SLSU\ClinicController@remove');

  Route::post("/upload", $controller_path . '\SLSU\ClinicController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\ClinicController@studentsearch');
});

//clearance osas
Route::group(['prefix' => 'osas', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\OSASController@search');

  Route::post("/add", $controller_path . '\SLSU\OSASController@add');
  Route::post("/remove", $controller_path . '\SLSU\OSASController@remove');

  Route::post("/upload", $controller_path . '\SLSU\OSASController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\OSASController@studentsearch');
});

//clearance registrar
Route::group(['prefix' => 'registrar', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\RegistrarController@search');

  Route::post("/add", $controller_path . '\SLSU\RegistrarController@add');
  Route::post("/remove", $controller_path . '\SLSU\RegistrarController@remove');

  Route::post("/upload", $controller_path . '\SLSU\RegistrarController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\RegistrarController@studentsearch');
});

//clearance bargo
Route::group(['prefix' => 'bargo', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\BARGOController@search');

  Route::post("/add", $controller_path . '\SLSU\BARGOController@add');
  Route::post("/remove", $controller_path . '\SLSU\BARGOController@remove');

  Route::post("/upload", $controller_path . '\SLSU\BARGOController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\BARGOController@studentsearch');
});

//clearance mis
Route::group(['prefix' => 'mis', 'middleware' => ['auth','clearance']], function () use ($controller_path){

  Route::get('/autocomplete', $controller_path . '\SLSU\MISController@search');

  Route::post("/add", $controller_path . '\SLSU\MISController@add');
  Route::post("/remove", $controller_path . '\SLSU\MISController@remove');

  Route::post("/upload", $controller_path . '\SLSU\MISController@upload');

  Route::post("/studentsearch", $controller_path . '\SLSU\MISController@studentsearch');
});


//grade
Route::group(['prefix' => 'grade', 'middleware' => ['auth','teacher']], function () use ($controller_path){
  Route::post('/save', $controller_path . '\SLSU\GradesController@save')->name('save-grades');
  Route::post('/bulk', $controller_path . '\SLSU\GradesController@upload')->name('save-grades');
});

//export
Route::group(['prefix' => 'teacher', 'middleware' => ['auth','teacher']], function () use ($controller_path){
  Route::get('/class-record', $controller_path . '\SLSU\ExportController@classrecord')->name('classrecord');
});

Route::group(['prefix' => 'registrar', 'middleware' => ['auth','registrar']], function () use ($controller_path){
  Route::get('/qfin67', $controller_path . '\SLSU\ExportController@qfin67')->name('qfin67');
});



//SMS
Route::group(['prefix' => 'sms', 'middleware' => ['auth']], function () use ($controller_path){
  Route::post('/bulk', $controller_path . '\SLSU\SMSController@bulksms')->name('bulksms');
  Route::post('/one', $controller_path . '\SLSU\SMSController@onesms')->name('onesms');
});


//Report
Route::group(['prefix' => 'report', 'middleware' => ['auth']], function () use ($controller_path){
  Route::post('/gradesheet', $controller_path . '\SLSU\Report\ReportController@gradesheet')->name('gradesheet');
  Route::post('/workload', $controller_path . '\SLSU\Report\ReportController@workload')->name('gradesheet');
  Route::post('/certificate', $controller_path . '\SLSU\Report\ReportController@certificate');
  Route::get('/qfin65', $controller_path . '\SLSU\Report\ReportController@qfin65')->name('report-qfin65');

  Route::group(['middleware' => ['enrol']], function () use ($controller_path){
  //count
    Route::get('/count', $controller_path . '\SLSU\Report\ReportController@count')->name('enrolment-count');
    Route::post('/count', $controller_path . '\SLSU\Report\ReportController@count')->name('enrolment-count');
  });
});


// layout
Route::get('/layouts/without-menu', $controller_path . '\layouts\WithoutMenu@index')->name('layouts-without-menu');
Route::get('/layouts/without-navbar', $controller_path . '\layouts\WithoutNavbar@index')->name('layouts-without-navbar');
Route::get('/layouts/fluid', $controller_path . '\layouts\Fluid@index')->name('layouts-fluid');
Route::get('/layouts/container', $controller_path . '\layouts\Container@index')->name('layouts-container');
Route::get('/layouts/blank', $controller_path . '\layouts\Blank@index')->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', $controller_path . '\pages\AccountSettingsAccount@index')->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', $controller_path . '\pages\AccountSettingsNotifications@index')->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', $controller_path . '\pages\AccountSettingsConnections@index')->name('pages-account-settings-connections');
Route::get('/pages/misc-error', $controller_path . '\pages\MiscError@index')->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', $controller_path . '\pages\MiscUnderMaintenance@index')->name('pages-misc-under-maintenance');



// cards
Route::get('/cards/basic', $controller_path . '\cards\CardBasic@index')->name('cards-basic');

// User Interface
Route::get('/ui/accordion', $controller_path . '\user_interface\Accordion@index')->name('ui-accordion');
Route::get('/ui/alerts', $controller_path . '\user_interface\Alerts@index')->name('ui-alerts');
Route::get('/ui/badges', $controller_path . '\user_interface\Badges@index')->name('ui-badges');
Route::get('/ui/buttons', $controller_path . '\user_interface\Buttons@index')->name('ui-buttons');
Route::get('/ui/carousel', $controller_path . '\user_interface\Carousel@index')->name('ui-carousel');
Route::get('/ui/collapse', $controller_path . '\user_interface\Collapse@index')->name('ui-collapse');
Route::get('/ui/dropdowns', $controller_path . '\user_interface\Dropdowns@index')->name('ui-dropdowns');
Route::get('/ui/footer', $controller_path . '\user_interface\Footer@index')->name('ui-footer');
Route::get('/ui/list-groups', $controller_path . '\user_interface\ListGroups@index')->name('ui-list-groups');
Route::get('/ui/modals', $controller_path . '\user_interface\Modals@index')->name('ui-modals');
Route::get('/ui/navbar', $controller_path . '\user_interface\Navbar@index')->name('ui-navbar');
Route::get('/ui/offcanvas', $controller_path . '\user_interface\Offcanvas@index')->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', $controller_path . '\user_interface\PaginationBreadcrumbs@index')->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', $controller_path . '\user_interface\Progress@index')->name('ui-progress');
Route::get('/ui/spinners', $controller_path . '\user_interface\Spinners@index')->name('ui-spinners');
Route::get('/ui/tabs-pills', $controller_path . '\user_interface\TabsPills@index')->name('ui-tabs-pills');
Route::get('/ui/toasts', $controller_path . '\user_interface\Toasts@index')->name('ui-toasts');
Route::get('/ui/tooltips-popovers', $controller_path . '\user_interface\TooltipsPopovers@index')->name('ui-tooltips-popovers');
Route::get('/ui/typography', $controller_path . '\user_interface\Typography@index')->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', $controller_path . '\extended_ui\PerfectScrollbar@index')->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', $controller_path . '\extended_ui\TextDivider@index')->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', $controller_path . '\icons\Boxicons@index')->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', $controller_path . '\form_elements\BasicInput@index')->name('forms-basic-inputs');
Route::get('/forms/input-groups', $controller_path . '\form_elements\InputGroups@index')->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', $controller_path . '\form_layouts\VerticalForm@index')->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', $controller_path . '\form_layouts\HorizontalForm@index')->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', $controller_path . '\tables\Basic@index')->name('tables-basic');
