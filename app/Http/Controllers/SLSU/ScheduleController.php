<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SLSU\Preference;
use Illuminate\Support\Facades\Schema;

use Exception;
use ROLE;
use GENERAL;
use Crypt;

use App\Models\Prospectos;


class ScheduleController extends Controller
{

    protected $sy;
    protected $sem;

    public function __construct(){

        $pref = GENERAL::getStudentDefaultEnrolment();
        if (empty($pref))
          return GENERAL::Error("Student preference not set");

        $this->sy = $pref['SchoolYear'];
        $this->sem = $pref['Semester'];
    }

    public function lists($data = []){

        $id = $data['ID'];
        if (!empty($data['SY'])){
          $this->sy = $data['SY'];
        }

        if (!empty($data['SEM'])){
          $this->sem = $data['SEM'];
        }

        $ids = [];
        $prefs = new Preference();
        $pref = $prefs->GetDefaults(['AllowCrossEnrol']);
        $AllowCrossEnrol = $prefs->GetDefaultValue($pref,'AllowCrossEnrol');

        $pros = Prospectos::where("pri", $id)->first();
        if (empty($pros))
            return [];

        $lec = (empty($pros->lec)?0:$pros->lec);
        $lab = (empty($pros->lab)?0:$pros->lab);
        $alias = (empty($pros->TitleAlias)?"":$pros->TitleAlias);
        $title = (empty($pros->coursetitle)?"":$pros->coursetitle);

        $pullschedtmp = Prospectos::query();
        if (empty($AllowCrossEnrol)){
          $pullschedtmp->where("course", $pros->course);
        }
        $pullschedtmp->where("coursetitle", trim($pros->coursetitle))
              ->where("lab", $lab)
              ->where("lec", $lec);
        $pullsched = $pullschedtmp->get();

        foreach($pullsched as $al){
          array_push($ids, $al->id);
        }

        if (!empty($alias)){

            $getAliases = Prospectos::query();
            if (empty($AllowCrossEnrol)){
              $getAliases = $getAliases->where("course", $pros->course);
            }
            $getAliases = $getAliases->where("lab", $lab)
                          ->where("lec", $lec)
                          ->where("hide","<>",1);

            $tmp = explode(";", $alias);

            if (sizeof($tmp) == 1){
              // dd($alias);
              $getAliases = $getAliases->where("coursetitle", trim($alias));
            }else{
                $getAliases = $getAliases->where(function($q) use($tmp){
                  foreach($tmp as $tm){
                    $q = $q->orWhere('coursetitle', $tm);
                  }
                });
            }

            $getAliases = $getAliases->get();
            foreach($getAliases as $al){
              array_push($ids, $al->id);
            }
        }

        $cc = "courseoffering".$this->sy.$this->sem;
        $g = "grades".$this->sy.$this->sem;
        if (!Schema::connection(strtolower(session('campus')))->hasTable($cc))
          return [];
        if (empty($ids))
          return [];

        $schedules = DB::connection(strtolower(session('campus')))
            ->table($cc . " as cc")
            ->select("cc.*", "st1.tym as Time1", "st2.tym as Time2",
              DB::connection(strtolower(session('campus')))->raw('count(g.id) as cEnrolled'))
            ->leftjoin("schedule_time as st1", "cc.sched", "=", "st1.id")
            ->leftjoin("schedule_time as st2", "cc.sched2", "=", "st2.id")
            ->leftjoin($g.' as g', 'cc.id', '=','g.courseofferingid')
            ->whereIn("courseid", $ids)
            ->where('cc.avail', 0)
            ->groupBy('cc.id')
            ->get();

        return $schedules;

    }

    public function getDayinList($lists, $day){
        $out = "";
        foreach($lists as $list){
            if ($list->DayOfWeek == $day){
                $out = $list->FullName;
            }
        }
        return $out;
    }

    public function getDayList(){
        $list = DB::connection(strtolower(session('campus')))->table("listday")
            ->whereNull("deleted_at")
            ->get();
        return $list;
    }

    public function isTimeConflict($data=[], $sched = []){
        $out = [];
        $conflict = [];
        $isConflict = false;
        $listofdays = $this->getDayList();
        $listofdaystoadd = [];

        $myTimes = [];
        foreach($data as $mytime){
            if (!empty($mytime['Time1'])){
                array_push($myTimes, ["Time" => $mytime['Time1'], "CourseNo" => $mytime['CourseNo'], "Sched" => 1]);
            }
            if (!empty($mytime['Time2'])){
                array_push($myTimes, ["Time" => $mytime['Time2'], "CourseNo" => $mytime['CourseNo'], "Sched" => 2]);
            }
        }


        $check = false;
        // dd($sched);
        if (!empty($sched->Time1)){
            // dd($sched);
            $check = true;
            $tmpTime1 = explode(" ", $sched->Time1);
            $listofdaystoadd = $this->getDayinList($listofdays, $tmpTime1[1]);
            $listofdaystoadd = explode(",", $listofdaystoadd);
        }


        foreach ($myTimes as $myTime){
            $tmpDBTime1 = explode(" ", $myTime['Time']);
            $listofdayfromdb = $this->getDayinList($listofdays, $tmpDBTime1[1]);
            $listofdayfromdb = explode(",", $listofdayfromdb);
            $match = false;
            foreach ($listofdaystoadd as $valDay) {
                if (in_array($valDay, $listofdayfromdb))
                {
                    $match = true;
                }
            }

            if ($match){
                $tym2tmpstart = $tmpDBTime1[2]." ".$tmpDBTime1[3];
                $tym2tmpend = $tmpDBTime1[4]." ".$tmpDBTime1[5];

                //Start Time
                $stym2start = $this->convertToMinutes($tym2tmpstart);
                $stym2end = $this->convertToMinutes($tym2tmpend);


                //convert to minutes from input sched
                //Start Time
                $stym1start = $this->convertToMinutes($tmpTime1[2]." ".$tmpTime1[3]);
                //echo "<br>";
                $stym1end = $this->convertToMinutes($tmpTime1[4]." ".$tmpTime1[5]);

                $overlap = ($stym1start < $stym2end) && ($stym2start < $stym1end);

                if ($overlap){
                    $isConflict = true;
                    array_push($out,[
                      'CourseNo' => $myTime['CourseNo'],
                      'Schedule' =>  $myTime['Sched'],
                      'Time' => $myTime['Time']
                    ]);
                }
            }
        }

        $conflict = ['Conflict' => $isConflict, "Message" => $out];

        return $conflict;
    }

    public function convertToMinutes($tym){
      $tym = explode(" ", $tym);

      $tym2 = explode(":", $tym[0]);
      $hour = $tym2[0];
      $minute = $tym2[1];

      switch (strtolower($tym[1])) {
        case 'pm':
          if ($hour != 12){
            $hour += 12;
          }
          break;
      }

		  return ($hour * 60) +  $minute;
	  }

}
