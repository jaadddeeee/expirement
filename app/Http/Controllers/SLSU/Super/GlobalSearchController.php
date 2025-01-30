<?php

namespace App\Http\Controllers\SLSU\Super;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Preferences;
use Illuminate\Contracts\Encryption\DecryptException;

use Exception;
use GENERAL;
use Crypt;

use App\Models\Role;
use App\Models\Course;

class GlobalSearchController extends Controller
{

    public function startsearch(Request $request){

      $pageTitle = "Global Search";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.super.globalprosearch',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'str' => $request->str
      ]);

    }

    public function globalsearch(Request $request){

        $out = [];
        $str = $request->str;
        // dd($str);

        if (auth()->user()->AllowSuper == 1 or \ROLE::isRegistrar()){
          foreach(GENERAL::Campuses() as $index => $campus){
              $res = DB::connection(strtolower($index))
                  ->table("students as s")
                  ->select('s.StudentNo','s.FirstName','s.MiddleName','s.LastName','c.accro','s.cur_num')
                  ->leftjoin("course as c", "s.Course", "=", "c.id")
                  ->where(function($query) use ($str){
                    $query->orwhere("LastName","LIKE","%{$str}%")
                      ->orwhere("FirstName","LIKE","%{$str}%")
                      ->orwhere("StudentNo","LIKE","%{$str}%");
                })
                ->orderBy('LastName')
                ->orderBy('FirstName')
                ->get();

              if (sizeof($res)> 0){
                  foreach($res as $r){
                      array_push($out,[
                        "StudentNo" =>$r->StudentNo,
                        "FirstName" => $r->FirstName,
                        'MiddleName' => $r->MiddleName,
                        "LastName" => $r->LastName,
                        "Course" => $r->accro,
                        'Campus' => $campus['Campus'],
                        'CampusIndex' => $index,
                        'CurNum' => $r->cur_num
                      ]);
                  }

              }
          }
        }elseif (\ROLE::isDepartment()){

            $myrole = Role::where("EmpID", auth()->user()->Emp_No)
                ->where("Role", "Department")
                ->first();

            $courses = Course::where("Department", $myrole->DepartmentID)
              ->pluck('id')->toArray();

            $res = DB::connection(strtolower(session('campus')))
              ->table("students as s")
              ->select('s.StudentNo','s.FirstName','s.MiddleName','s.LastName','c.accro','s.cur_num')
              ->leftjoin("course as c", "s.Course", "=", "c.id")
              ->where(function($query) use ($str){
                $query->orwhere("LastName","LIKE","%{$str}%")
                  ->orwhere("FirstName","LIKE","%{$str}%")
                  ->orwhere("StudentNo","LIKE","%{$str}%");
              })
              ->whereIn('Course', $courses)
              ->orderBy('LastName')
              ->orderBy('FirstName')
              ->get();

          if (sizeof($res)> 0){
              foreach($res as $r){
                  array_push($out,[
                    "StudentNo" =>$r->StudentNo,
                    "FirstName" => $r->FirstName,
                    'MiddleName' => $r->MiddleName,
                    "LastName" => $r->LastName,
                    "Course" => $r->accro,
                    'Campus' => GENERAL::Campuses()[session('campus')]['Campus'],
                    'CampusIndex' => session('campus'),
                    'CurNum' => $r->cur_num
                  ]);
              }

          }
        }


        return view('slsu.super.globalsearch', compact('out'),[
          'str' => $str
        ]);
    }

    public function globalone(Request $request){

        try{

            $studentnumber = Crypt::decryptstring($request->snum);
            $campus = Crypt::decryptstring($request->campus);

            $one = DB::connection(strtolower($campus))
                ->table('students as s')
                ->select("s.*", "c.accro", "m.course_major")
                ->leftjoin("course as c", "s.Course", "=", "c.id")
                ->leftjoin("major as m", "s.major", "m.id")
                ->where("s.StudentNo", $studentnumber)
                ->first();

            $onesub = DB::connection(strtolower($campus))
                ->table('students2 as s')
                ->where("s.StudentNo", $studentnumber)
                ->first();

            $pageTitle = utf8_decode(strtoupper($one->FirstName . (empty($one->MiddleName)?' ':' '.$one->MiddleName[0].'. ') .$one->LastName));
            $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

            return view('slsu.super.onestudent',compact('one','onesub'), [
                  'pageTitle' => $pageTitle,
                  'headerAction' => $headerAction,
                  'str' => $request->str,
                  'campus' => $campus
            ]);

        }catch(DecryptException $e){
            return "Invalid hash.";
        }

    }


    public function grades(Request $request){
      try{
          $infos = [];
          $id = Crypt::decryptstring($request->id);
          $campus = Crypt::decryptstring($request->campus);

          $registrations = DB::connection(strtolower($campus))
          ->table('registration as r')
          ->select("r.*", "c.accro", "m.course_major")
          ->leftjoin("major as m", "r.Major", "m.id")
          ->leftjoin("course as c", "r.Course", "c.id")
          ->where("r.StudentNo", $id)
          ->orderby("SchoolYear", "DESC")
          ->orderby("Semester", "DESC")
          ->get();

          // dd($registrations);
          foreach($registrations as $reg){
            $grades = [];
            $g = "grades".$reg->SchoolYear.$reg->Semester;
            $cc = "courseoffering".$reg->SchoolYear.$reg->Semester;
            if (Schema::connection(strtolower($campus))->hasTable($g) and Schema::connection(strtolower($campus))->hasTable($cc))  {
                $grades = DB::connection(strtolower($campus))
                  ->table($g.' as g')
                  ->select('g.*', 't.units','t.courseno','t.coursetitle','t.exempt', 'e.LastName','e.FirstName','e.MiddleName','t.ExcludeinAVG')
                  ->leftjoin("transcript as t", "g.sched", "=", "t.id")
                  ->leftjoin($cc." as cc", "g.courseofferingid", "=", "cc.id")
                  ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
                  ->where("gradesid", $reg->RegistrationID)
                  ->orderby("t.sort_order")
                  ->get();
            }

            array_push($infos, [
              'Course' => $reg->accro,
              'Major' => $reg->course_major,
              'Finalize' =>$reg->finalize,
              'SchoolYear' => $reg->SchoolYear,
              'StudentYear' => $reg->StudentYear,
              'Section' => $reg->Section,
              'Semester' => $reg->Semester,
              'Status' => $reg->StudentStatus,
              'Subjects' => $grades
            ]);
          }
          return view('slsu.super.grades', compact('infos'),[
            'Campus' => $campus
          ]);
      }catch(Exception $e){
          return response()->json(['errors' => $e->getMessage()],400);
      }catch(DecryptException $e){
          return response()->json(['errors' => $e->getMessage()],400);
      }
    }
}
