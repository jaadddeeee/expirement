<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\Registration;
use App\Models\Credited;
use App\Models\MaxLimit;
use App\Models\MaxLimitByPass;
use App\Models\BypassClearance;
use DateTime;
class GlobalDeclare {

  public static function API(){
      // $out = [
      //   'URL' => "http://192.168.0.11/hrmis",
      //   'Token' => '1988|m6eWdBaVMw0HdwYVXD0QfS2wPOjiI7XyfdtW6W4G'
      // ];

      $out = [
        'URL' => "https://api.southernleytestateu.edu.ph",
        'Token' => '14240|DCz8jRB7WZUZswmk7tlIfYcQwkdNUiwYJcjC0F7z',
        'ClientID' => '976011913287-r8e3dspqvfh0vf1m77sa367k3t3jtv0p.apps.googleusercontent.com'
      ];

      return $out;
  }

  public static function Campuses(){
      $campuses = [
          'SG' => ['ID' => 1, "Campus" => "Main Campus", "Icon" => "fa-cogs","Color" => "primary"],
          'MCC' => ['ID' => 2, "Campus" => "Maasin City Campus", "Icon" => "fa-users","Color" => "danger"],
          'TO' => ['ID' => 3, "Campus" => "Tomas Oppus Campus", "Icon" => "fa-graduation-cap","Color" => "info"],
          'BN' => ['ID' => 4, "Campus" => "Bontoc Campus", "Icon" => "fa-ship","Color" => "primary"],
          'SJ' => ['ID' => 5, "Campus" => "San Juan Campus", "Icon" => "fa-briefcase","Color" => "danger"],
          'HN' => ['ID' => 6, "Campus" => "Hinunangan Campus", "Icon" => "fa-leaf","Color" => "success"],
      ];
      return $campuses;
  }

  public static function CampusbyNo($code){
      $out = 0;
      switch($code){
          case 1:
            $out = "SG";
            break;
          case 2:
            $out = "MCC";
            break;
          case 3:
            $out = "TO";
            break;
          case 4:
            $out = "BN";
            break;
          case 5:
            $out = "SJ";
            break;
          case 6:
            $out = "HN";
            break;
      }

      return $out;
  }

  public static function Sender(){
    $out = "SLSU";
    if (strtolower(session('campus')) != "sg")
      $out = "SLSU-".strtoupper(session('campus'));
    return "SLSU";
  }

  public static function SchoolYears(){
      $out = [];
      for($sy=date('Y'); $sy>=1985; $sy--){
        array_push($out, $sy);
      }
      return $out;

  }

  public static function Semesters(){
    $out = [
      1 => ["Long" => "First Semester", "Short" => "1st"],
      2 => ["Long" => "Second Semester", "Short" => "2nd"],
      9 => ["Long" => "Summer", "Short" => "Sum"],
      10 => ["Long" => "Summer 2", "Short" => "Sum2"]
    ];
    return $out;

  }

  public static function numtoMonths($i,$format=""){
    $monthNum  = $i;
    $dateObj   = DateTime::createFromFormat('!m', $monthNum);
    if (empty($format) or $format == "long")
      $monthName = $dateObj->format('F');
    elseif ($format == "short")
      $monthName = $dateObj->format('M');
    else
      $monthName = $dateObj->format('F');

    return $monthName;
  }

  public static function Gender(){
    $out = [
      'M' => 'Male',
      'F' => 'Female',
    ];
    return $out;

  }

  public static function YearStanding(){
    $out = [
      1 => ['Short' => '1st Year', 'Long' => 'First Year'],
      2 => ['Short' => '2nd Year', 'Long' => 'Second Year'],
      3 => ['Short' => '3rd Year', 'Long' => 'Third Year'],
      4 => ['Short' => '4th Year', 'Long' => 'Fourth Year'],
      5 => ['Short' => '5th Year', 'Long' => 'Fifth Year'],
    ];

    return $out;
  }

  public static function Scholarships(){
    $out = [
      1 => ["Description" => "Percentage"],
      2 => ["Description" => "Fix Amount - Tuition Only"],
      3 => ["Description" => "Fix Amount - Amount Due"],
    ];
    return $out;
  }

  public static function ScholarshipsNew(){
    $out = [
      1 => ["Description" => "Internal"],
      2 => ["Description" => "External"],
    ];
    return $out;
  }

  public static function ExternalSchType(){
    $out = [
      1 => ["Description" => "Private"],
      2 => ["Description" => "Local"],
      3 => ["Description" => "National"],
    ];
    return $out;
  }

  public static function GradeRemarksString($f, $color = 0 , $bold = ''){

      if ($f > 0 and $f <= 3){
          if (empty($color))
            $c = "PASSED";
          else
            $c = '<span class = "text-dark '.$bold.'">PASSED</span>';
      }else if ($f == 9.9){
          if (empty($color))
            $c = "DROPPED";
          else
            $c = '<span class = "text-danger '.$bold.'">DROPPED</span>';
      }else if ($f == 5){
          if (empty($color))
            $c = "FAILED";
          else
            $c = '<span class = "text-danger '.$bold.'">FAILED</span>';
      }else if ($f == 8.8){
          if (empty($color))
            $c = "INCOMPLETE";
          else
            $c = '<span class = "text-success '.$bold.'">INCOMPLETE</span>';
      }else if ($f == 7.7){
          if (empty($color))
            $c = "PASSED";
          else
            $c = '<span class = "text-dark '.$bold.'">PASSED</span>';
      }else if ($f == 6.6){
          if (empty($color))
            $c = "INPROGRESS";
          else
            $c = '<span class = "text-warning '.$bold.'">INPROGRESS</span>';
      }else{
          if (empty($color))
            $c = "";
          else
            $c = '';
      }

      return $c;
  }

  public static function GradeRemarks($f, $color = 0 , $bold = ''){

      if ($f == ""){
        $c = "";
      }else{
        if ($f > 0 and $f <= 3){
            if (empty($color))
              $c = number_format($f,1,'.',',');
            else
              $c = '<span class = "text-dark '.$bold.'">'.number_format($f,1,'.',',').'</span>';
        }else if ($f == 9.9){
            if (empty($color))
              $c = "Dr";
            else
              $c = '<span class = "text-danger '.$bold.'">Dr</span>';
        }else if ($f == 5){
            if (empty($color))
              $c = "5.0";
            else
              $c = '<span class = "text-danger '.$bold.'">5.0</span>';
        }else if ($f == 8.8){
            if (empty($color))
              $c = "INC";
            else
              $c = '<span class = "text-success '.$bold.'">INC</span>';
        }else if ($f == 7.7){
            if (empty($color))
              $c = "PSD";
            else
              $c = '<span class = "text-dark '.$bold.'">PSD</span>';
        }else if ($f == 6.6){
            if (empty($color))
              $c = "INP";
            else
              $c = '<span class = "text-warning '.$bold.'">INP</span>';
        }else{
            if ($f=="0.0"){
              $f = 0;
            }

            if (empty($f)){
              $f = 0;
            }

            if (empty($color)){
              // $c = number_format($f,1,'.',',');
              $c = $f;
            }else{
              $c = '<span class = "text-info '.$bold.'">'.$f.'</span>';
              // $c = '<span class = "text-info '.$bold.'">'.number_format($f,1,'.',',').'</span>';
            }
        }
      }

      return $c;
  }

  public static function Error($msg){
    return '<div  class = "alert alert-danger"><i class = "fa fa-exclamation-triangle"></i> '.$msg.'</div>';
  }

  public static function Success($msg){
    return '<div  class = "alert alert-success"><i class = "fa fa-exclamation-triangle"></i> '.$msg.'</div>';
  }

  public static function isMobile(){

    $useragent=$_SERVER['HTTP_USER_AGENT'];
    $mobile = false;
    if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
      $mobile = true;
    }

    return $mobile;
  }

  public static function setSchoolYearLabel($sy,$sem,$campus = ""){
      $out = "";
      if (empty($campus)){
        $campus = session('campus');
      }
      if (strtolower($campus)=="to"){
          if ($sem == 9){
              $out = $sy+1;
          }else{
              $out = $sy."-".($sy+1);
          }
      }else{
          if ($sem == 9){
              $out = $sy;
          }else{
              $out = $sy."-".($sy+1);
          }
      }

      return $out;
  }

  public static function validGrades($gg, $term){
    if ($gg == ""){
        return false;
    }else{
        if ($gg >= 1 &&  $gg <= 3){
            return true;
        }else if ($gg == "6.6" || $gg == "7.7" || $gg == "8.8" || $gg == "9.9"){
            return true;
        }else if ($gg == 5){
            return true;
        }else if ($gg > 3 && $gg <= 3.5 && $term == "mt"){
            return true;
        }else{
            return false;
        }
    }
  }

  public static function Logo(){
    return "images/logo/logo.png";
  }

  public static function Pilipinas(){
    return "images/logo/bagongpilipinas.png";
  }

  public static function ISOLogo(){
    return "images/logo/isologo.jpg";
  }

  public static function QStarLogo(){
    return "images/logo/qs.png";
  }

  public static function Greeting(){
        $out = "";
        /* This sets the $time variable to the current hour in the 24 hour clock format */
        $time = date("H");
        /* Set the $timezone variable to become the current timezone */
        $timezone = date("e");
        /* If the time is less than 1200 hours, show good morning */
        if ($time < "12") {
          $out =  "Good Morning";
        } else
        /* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */
        if ($time >= "12" && $time < "17") {
          $out =  "Good Afternoon";
        } else
        /* Should the time be between or equal to 1700 and 1900 hours, show good evening */
        if ($time >= "17" && $time < "19") {
          $out =  "Good Evening";
        } else
        /* Finally, show good night if the time is greater than or equal to 1900 hours */
        if ($time >= "19") {
          $out =  "Good Night";
        }

        return $out;

  }

  public static function getIp(){
      foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
          if (array_key_exists($key, $_SERVER) === true){
              foreach (explode(',', $_SERVER[$key]) as $ip){
                  $ip = trim($ip); // just to be safe
                  if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                      return $ip;
                  }
              }
          }
      }
      return request()->ip(); // it will return the server IP if the client IP is not found using this method.
  }

  public static function ComputeForGWA($final, $inc, $u){
      $unit = 0;
      $grade = 0;
      $countUnit = 0;
      if ($final > 0 and $final <= 3){
          $unit = $u;
          $grade = $final;
          $countUnit = $u;
      }else if ($final == 9.9){
          $unit = $u;
          $grade = 5;
          $countUnit = 0;
      }else if ($final == 5){
          $unit = $u;
          $grade = 5;
          $countUnit = 0;
      }else if ($final == 8.8){
          if ($inc > 0 and $inc <= 3){
              $unit = $u;
              $grade = $inc;
              $countUnit = $u;
          }elseif($inc == 5){
              $unit = $u;
              $grade = 5;
              $countUnit = 0;
          }else{
              $unit = 0;
              $grade = 0;
              $countUnit = 0;
          }
      }else if ($final > 3.0 and $final <= 3.4){
          if ($inc > 0 and $inc <= 3){
              $unit = $u;
              $grade = $inc;
              $countUnit = $u;
          }elseif($inc == 5){
              $unit = $u;
              $grade = 5;
              $countUnit = 0;
          }else{
              $unit = 0;
              $grade = 0;
              $countUnit = 0;
          }
      }else if ($final == 7.7){
          $unit = $u;
          $grade = $final;
          $countUnit = $u;
      }else{
          $unit = 0;
          $grade = 0;
          $countUnit = 0;
      }

      $result = $unit * $grade;
      $UnitsEarned = $countUnit;
      $runningUnit = $unit;

      return [
        "RunningTimes" => $result,
        "UnitsEarned" => $UnitsEarned,
        "RunningUnit" => $runningUnit
      ];
  }

  public static function getStudentDefaultEnrolment(){
      $out = [];

      $campus = 'sg';
      if (session('campus') !== null){
        $campus = session('campus');
      }

      $pref = DB::connection(strtolower($campus))
        ->table("preferences")
        ->where("UserType", "Student")
        ->first();

      if (!empty($pref))
        $out = [
          'SchoolYear' => $pref->SchoolYear,
          'Semester' => $pref->Semester
        ];

      return $out;
  }

  public static function ProspectosLabel($data = []){
      $slevel = $data['SchoolLevel'];
      $syear = $data['StudentYear'];
      if ($slevel == "Under Graduate"){
          switch($syear){
          case 1: $indi_year2 = "First Year"; break;
          case 2: $indi_year2 = "Second Year"; break;
          case 3: $indi_year2 = "Third Year"; break;
          case 4: $indi_year2 = "Fourth Year"; break;
          case 5: $indi_year2 = "Fift Year"; break;}
          $str = $indi_year2.' - '.(self::Semesters()[$data['Semester']]['Long']);
      }elseif ($slevel == "Doctoral"){
          switch($syear){
          case 1: $indi_year2 = "I. BASIC COURSES"; break;
          case 2: $indi_year2 = "II. MAJOR COURSES"; break;
          case 3: $indi_year2 = "III. ELECTIVE COURSES"; break;
          case 4: $indi_year2 = "IV. FOREIGN LANGUAGES"; break;
          case 5: $indi_year2 = "V. DISSERTATION"; break;}
          $str = $indi_year2;
      }elseif  ($slevel == "Masteral"){
          switch($syear){
          case 1: $indi_year2 = "I. BASIC COURSES"; break;
          case 2: $indi_year2 = "II. MAJOR COURSES"; break;
          case 3: $indi_year2 = "III. ELECTIVE COURSES"; break;
          case 4: $indi_year2 = "IV. THESIS WRITING"; break;
          case 5: $indi_year2 = ""; break;}
          $str = $indi_year2;
      }elseif  ($slevel == "Senior High School"){
          switch($syear){
          case 11: $indi_year2 = "GRADE 11"; break;
          case 12: $indi_year2 = "GRADE 12"; break;}
          $str = $indi_year2;
      }elseif  ($slevel == "High School" or $slevel == "Highschool"){
          switch($syear){
          case 7: $indi_year2 = "GRADE 7"; break;
          case 8: $indi_year2 = "GRADE 8"; break;
          case 9: $indi_year2 = "GRADE 9"; break;
          case 10: $indi_year2 = "GRADE 10"; break;}
          $str = $indi_year2;
      }elseif  ($slevel == "Elementary"){
          switch($syear){
          case 1: $indi_year2 = "GRADE 1"; break;
          case 2: $indi_year2 = "GRADE 2"; break;
          case 3: $indi_year2 = "GRADE 3"; break;
          case 4: $indi_year2 = "GRADE 4"; break;
          case 5: $indi_year2 = "GRADE 5"; break;
          case 6: $indi_year2 = "GRADE 6"; break;}
          $str = $indi_year2;
      }elseif  ($slevel == "Pre-Elem"){
          switch($syear){
          case 1: $indi_year2 = "TODDLER"; break;
          case 2: $indi_year2 = "NURSERY"; break;
          case 3: $indi_year2 = "KINDER 1"; break;
          case 4: $indi_year2 = "KINDER 2"; break;}
          $str = $indi_year2;
      }

      return $str;
  }

  public static function  getPrerequisite($transcripts = [], $ids = ""){
    $out = "";
    if (!empty($ids)){
        $tmp = explode(",", $ids);
        foreach ($tmp as $id){
            foreach($transcripts as $transcript){
                if ($id == $transcript->id){
                    if (empty($out)){
                        $out = $transcript->courseno;
                    }else{
                        $out .= ", ".$transcript->courseno;
                    }
                }
            }
        }
    }
    return $out;
  }

  public static function isCredited($lists = [], $id = 0){
    $credited = false;
    foreach ($lists as $list){
        if ($list->courseno == $id){
            $credited = true;
        }
    }

    return $credited;
  }

  public static function getGradesinTMP($sub, $array){

    $out = [];
      foreach (array_reverse($array) as $res) {
        if ($res['sched'] == $sub){
          $out = $res;
                  return $out;
        }
      }
        return $out;
  }

  public static function isPrerequisiteOK($prerequisites,$studentno){
    $ok = true;
    if (!empty($prerequisites)){

        $tmpPre = explode(",", $prerequisites);
        $ctr = 0;
        $grades = self::CreateTMPGradesBySubjectID($studentno, $tmpPre);
        if (count($grades) <= 0)
          return false;

        foreach ($tmpPre as $tmpPrevalue) {
            $tmpP = GENERAL::getGradesinTMP($tmpPrevalue,$grades);
            $Preinc = (isset($tmpP['inc'])?$tmpP['inc']:"");
            $Prefinal = (isset($tmpP['final'])?$tmpP['final']:"");

            $tmpStatus = GENERAL::isGradePass($Prefinal,$Preinc);

            if ($tmpStatus != "passed"){
                $ctr++;
            }
        }

        if ($ctr > 0){
          $ok = false;
        }
    }

    return $ok;
  }

  public static function CreateTMPGradesBySubjectID($StudentNo, $ids){

    $tmpGrades = [];
    $studentno = $StudentNo;
    $sql = Registration::where("StudentNo", $studentno)->get();
    foreach ($sql as $reg) {
        $blgrades = "grades".$reg->SchoolYear.$reg->Semester;
        $tblCC = "courseoffering".$reg->SchoolYear.$reg->Semester;
        if (Schema::connection(strtolower(session('campus')))->hasTable($blgrades)) {
            $out = DB::connection(strtolower(session('campus')))->table($blgrades." as g")
                ->select('g.gradesid', 'g.inc', 'g.final', 'g.sched', 'g.StudentNo','g.courseofferingid',
                        "cc.coursecode","st1.tym as Time1","st2.tym as Time2",
                        "t.courseno","t.units", "t.exempt")
                ->leftjoin($tblCC." as cc", "g.courseofferingid","=", "cc.id")
                ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
                ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
                ->leftjoin("transcript as t", "g.sched", "=", "t.id")
                ->where("g.StudentNo", $studentno)
                ->whereIn("t.id", $ids)
                ->get();

            foreach ($out as $val){
                $col = array('gradesid' => $val->gradesid, 'inc' => $val->inc,
                      'final' =>$val->final, 'sched' => $val->sched, 'StudentNo' => $val->StudentNo,
                      'SchoolYear' => $reg->SchoolYear, 'Semester' => $reg->Semester,'cc'=>$val->courseofferingid, "Finalize" => $reg->finalize,
                      'CourseCode' => $val->coursecode, "Time1" => $val->Time1,"Time2" => $val->Time2, "CourseNo" => $val->courseno,
                      "Unit" => $val->units, "Exempt" => $val->exempt);
                array_push($tmpGrades, $col);
            }
        }

    }
    return $tmpGrades;
  }

  public static function CreateTMPGrades($data = []){

    $tmpGrades = [];
    $studentno = $data['StudentNo'];
    $sql = Registration::where("StudentNo", $studentno)->get();
    foreach ($sql as $reg) {
        $blgrades = "grades".$reg->SchoolYear.$reg->Semester;
        $tblCC = "courseoffering".$reg->SchoolYear.$reg->Semester;
        if (Schema::connection(strtolower(session('campus')))->hasTable($blgrades)) {
            $out = DB::connection(strtolower(session('campus')))->table($blgrades." as g")
                ->select('g.gradesid', 'g.inc', 'g.final', 'g.sched', 'g.StudentNo','g.courseofferingid',
                        "cc.coursecode","st1.tym as Time1","st2.tym as Time2",
                        "t.courseno","t.units", "t.exempt")
                ->leftjoin($tblCC." as cc", "g.courseofferingid","=", "cc.id")
                ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
                ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
                ->leftjoin("transcript as t", "g.sched", "=", "t.id")
                ->where("g.StudentNo", $studentno)
                ->get();

            foreach ($out as $val){
                $col = array('gradesid' => $val->gradesid, 'inc' => $val->inc,
                      'final' =>$val->final, 'sched' => $val->sched, 'StudentNo' => $val->StudentNo,
                      'SchoolYear' => $reg->SchoolYear, 'Semester' => $reg->Semester,'cc'=>$val->courseofferingid, "Finalize" => $reg->finalize,
                      'CourseCode' => $val->coursecode, "Time1" => $val->Time1,"Time2" => $val->Time2, "CourseNo" => $val->courseno,
                      "Unit" => $val->units, "Exempt" => $val->exempt);
                array_push($tmpGrades, $col);
            }
        }

    }
    return $tmpGrades;
  }

  public static function getCreditedSubjects($data = []){
      $credits = Credited::where("StudentNo", $data['StudentNo'])->get();
      return $credits;
  }

  public static function countEnrolled($id, $array){
    $c = 0;
    foreach ($array as $key) {
      if ($id ==$key['sched'])
        $c++;

    }
    return $c;
  }

  public static function isGradePass($f,$inc,$sy="",$sem="",$finalize = 0){

		$stat = "nograde";
		if ($f == "0"){
        $pref = self::getStudentDefaultEnrolment();
        if ($pref['SchoolYear'] == $sy and $pref['Semester'] == $sem){
            $stat = "current";
        }elseif ($finalize != 1){
            $stat = "notfinalize";
        }elseif ($finalize == 1){
            $stat = "tograde";
        }
		}elseif ($f >= 1 and $f <= 3){
            $stat = "passed";
        }elseif ($f == 5){
        	$stat = "failed";
        }elseif ($f == 9.9){
        	$stat = "failed";
        }elseif ($f == 7.7){
        	$stat = "passed";
        }elseif ($f == 6.6){
        	$stat = "inp";
        }elseif ($f == 8.8 and ($inc >= 1 and $inc <= 3)){
        	$stat = "passed";
        }elseif ($f == 8.8 and $inc == 5){
        	$stat = "failed";
        }elseif ($f == 8.8 and $inc == 9.9){
        	$stat = "failed";
        }elseif ($f == 8.8 and $inc == 0){
        	$stat = "inc";
        }

        // echo $finalize."<br>";
		return $stat;
	}

  public static function getMaxUnit($data = []){
    $ylvl = (isset($data['StudentYear'])?(empty($data['StudentYear'])?0:$data['StudentYear']):0);
    $col = "";
    switch($ylvl){
      case 1:
        $col = "First";
        break;
      case 2:
        $col = "Second";
        break;
      case 3:
        $col = "Third";
        break;
      case 4:
        $col = "Fourth";
        break;
      case 5:
        $col = "Fifth";
        break;
    }


    if (empty($col))
      $lim = 0;
    else{
      $MaxLimit = MaxLimit::where("CourseID", $data['Course'])
        ->where("Semester", $data['Semester'])
        ->first();

      $lim = 0;
      if (empty($ylvl) or empty($col)){
        $lim = 0;

      }else{
        if (!empty($MaxLimit))
          $lim = $MaxLimit->$col;
      }
    }

    $addUnits = MaxLimitByPass::where("StudentNo", $data['StudentNo'])
      ->where("SchoolYear", $data['SchoolYear'])
      ->where("Semester", $data['Semester'])
      ->first();
    $add = 0;
    if (!empty($addUnits)){
      $add = $addUnits->Units;
    }

    return $lim + $add;
  }

  public static function TotalEnrolledUnits(){
      $out = 0;

      if (count(session('MySubjects')) > 0){
          foreach(session('MySubjects') as $cur){
              if ($cur['Exempt'] != 1)
              $out += $cur['Unit'];
          }
      }

      return $out;
  }

  public static function countCurrentEnrolled(){
    $out = count(session('MySubjects'));
    return $out;
  }

  public static function canEditEnrolment($f){
    $out = true; // false b4
    //9 b4
    if ($f == 1)
      $out = false;

    if ($f == 0)
      $out = false;

    if ($f == 5)
      $out = false;


    return $out;
  }

  // public static function Avatar($id = "", $sex = "M", $tor = false){

  //     if (empty($id)){
  //       if (!$tor){
  //         if ($sex == "M" or $sex == "Male"){
  //           $url = "data:image/jpg;base64,".base64_encode(file_get_contents("images/face-male.jpg"));
  //         }else{
  //           $url = "data:image/jpg;base64,".base64_encode(file_get_contents("images/face-female.jpg"));
  //         }
  //       }else{
  //         $url = "";
  //       }
  //     }else{
  //       $url = rtrim($this->PathUpload(), "/")."/".$this->getFlag()."/TOR/upload/".$id;
  //       // var_dump($url);
  //       if (!file_exists($url)) {
  //         if (!$tor){
  //           if ($sex == "M" or $sex == "Male"){
  //             $url = "data:image/jpg;base64,".base64_encode(file_get_contents("images/face-male.jpg"));
  //           }else{
  //             $url = "data:image/jpg;base64,".base64_encode(file_get_contents("images/face-female.jpg"));
  //           }
  //         }else{
  //           $url = "";
  //         }

  //       }else{
  //         if (!$tor){
  //           $url = "data:image/jpg;base64,".base64_encode(file_get_contents($url));
  //         }
  //       }
  //     }



  //     return $url;
  // }

  public static function getEnrolledDB($data = []){
      $grades = "grades".$data['SchoolYear'].$data['Semester'];
      $cc = "courseoffering".$data['SchoolYear'].$data['Semester'];
      $out = true;
      $total = 0;
      $mysubjects = [];
      if (Schema::connection(strtolower(session('campus')))->hasTable($grades)) {
        $out = false;
        $gs = DB::connection(strtolower(session('campus')))->table($grades." as g")
            ->select("t.units","t.exempt","st1.tym as Time1", "st2.tym as Time2", "t.courseno")
            ->leftjoin($cc." as cc", "g.courseofferingid", "=", "cc.id")
            ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
            ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
            ->leftjoin("transcript as t", "g.sched", "=", "t.id")
            ->where("g.StudentNo", $data['StudentNo'])
            ->where("t.exempt", "<>", 1)
            ->get();

        foreach($gs as $g){
          array_push($mysubjects, [
            "Time1" => $g->Time1,
            "Time2" => $g->Time2,
            "CourseNo" => $g->courseno,
            "Unit" => $g->units,
            "Exempt" => $g->exempt]);

          $total += (empty($g->units)?0:$g->units);
        }
      }

      return ['Error' => $out, "Units" => $total, "MySubjects" => $mysubjects];
  }

  public static function isSubjectExist($data = []){
    $grades = "grades".$data['SchoolYear'].$data['Semester'];
    $error = true;
    $exist = true;
    if (Schema::connection(strtolower(session('campus')))->hasTable($grades)) {
      $error = false;
      $gs = DB::connection(strtolower(session('campus')))->table($grades." as g")
          ->where("sched", $data['SubjectID'])
          ->where("StudentNo", $data['StudentNo'])
          ->first();

      if (empty($gs)){
        $exist = false;
      }

    }

    return ['Error' => $error, 'Exist' => $exist];
  }

  public static function isCleared($studentno = "", $data = []){

		$cleared = true;
		if (empty($studentno))
			$cleared = false;

    $lists = BypassClearance::where("StudentNo", $studentno)
      ->first();

		if (!empty($lists)){
			$cleared = true;
		}else{

      if (!empty($data)){
        $ex = Registration::select('isCleared','SchoolLevel')
        ->where('StudentNo', $studentno)
        ->where('finalize', 1)
        ->where('isCleared', 2)
        ->whereNot("SchoolLevel", 'Highschool')
        ->where(function($q) use ($data){
            $q->where("SchoolYear", '<>', $data['SEM'])
            ->where("SchoolYear", '<>', $data['SEM']);
        })
        ->first();
      }else{
        $ex = Registration::select('isCleared','SchoolLevel')
        ->where('StudentNo', $studentno)
        ->where('finalize', 1)
        ->where('isCleared', 2)
        ->whereNot("SchoolLevel", 'Highschool')
        ->first();
      }

			if (!empty($ex)){
				if ($ex->SchoolLevel == "Highschool"){
					$cleared = true;
				}else{
					$cleared = false;
				}
			}

		}

		return $cleared;

	}

  public static function AdjectiveRating($num, $html = 'Yes'){
    $out = "INVLD";
    $color = 'danger';
    $num = round($num,0,PHP_ROUND_HALF_UP);
    switch ($num){
      case 1:
        $out = "Poor";
        break;
      case 2:
        $out = "Unsatisfactory";
        break;
      case 3:
        $out = "Satisfactory";
        $color = 'primary';
        break;
      case 4:
        $out = "Very Satisfactory";
        $color = 'success';
        break;
      case 5:
        $out = "Outstanding";
        $color = 'warning';
        break;
    }

    if ($html == 'Yes'){
      return "<span class = 'pt-5 text-".$color." fw-bold'>".$out."</span>";
    }else{
      return $out;
    }

  }

}
?>
