<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Prospectos;
use Crypt;
use Exception;
use GENERAL;

use App\Models\Enrolled;

class ClassRecord implements WithEvents
{

    protected $id;
    public function __construct(Request $request)
    {
        $id = $request->id;
        try{
          $this->id = Crypt::decryptstring($id);
          if (empty($this->id))
            return response()->json(['Error' => 'Invalid ID']);
        }catch(Exception $e){
            return response()->json(['Error' => 'Invalid hash']);
        }

    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $templateFile = new LocalTemporaryFile(\Storage::disk('local')->path('private/excel').'/classrecord2023.xlsx');
                $event->writer->reopen($templateFile, Excel::XLSX);

                $this->populatePage1($event);

                $event->writer->getSheetByIndex(1)->export($event->getConcernable()); // call the export on the first sheet
                $event->writer->createSheet(8);
                $sec = $event->writer->setActiveSheetIndex(8);
                $sec->setTitle("security");
                $sec->setSheetState('veryHidden');
                $sec->setCellValue('A1', Crypt::encryptstring($this->id));
                return $event->getWriter()->getSheetByIndex(1);
            },
        ];
    }

    private function populatePage1($event){

      $cc = "courseoffering".session('schoolyear').session("semester");

      $schedinfo = DB::connection(strtolower(session('campus')))
        ->table($cc)
        ->where('id', $this->id)
        ->first();

      $subinfo = Prospectos::find($schedinfo->courseid);

      $enrolled = new Enrolled();
      // dd();
      $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
        'c.accro','r.StudentYear','r.Section','r.finalize','s.StudentNo','s.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
        'sc1.tym as Time1', 'sc2.tym as Time2','c.course_title','cc.student_year','cc.section',
        'e.LastName as empLastName', 'e.FirstName as empFirstName', 'e.MiddleName as empMiddleName')
        ->where("courseofferingid", $this->id)
        ->where("r.SchoolYear", session('schoolyear'))
        ->where("r.Semester", session("semester"))
        ->where("r.finalize", 1)
        ->leftjoin($cc." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
        ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
        ->leftjoin("schedule_time as sc1", "cc.sched", "=", "sc1.id")
        ->leftjoin("schedule_time as sc2", "cc.sched2", "=", "sc2.id")
        ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
        ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
        ->leftjoin("course as c", "r.Course", "=", "c.id")
        ->leftjoin("transcript as t", $enrolled->getTable().".sched", "=", "t.id")
        ->orderBy("s.LastName")
        ->get();

        $sheet = $event->writer->getSheetByIndex(0);
        $sheet->setCellValue('B9', GENERAL::setSchoolYearLabel(session('schoolyear'), session('semester')));
        $sheet->setCellValue('B10', GENERAL::Semesters()[session('semester')]['Long']);

        $sheet->setCellValue('B12', $subinfo->courseno);
        $sheet->setCellValue('B13', $subinfo->coursetitle);
        $sheet->setCellValue('B14', $lists[0]->coursecode);

        $sheet->setCellValue('B16', $lists[0]->Time1 . (!empty($lists[0]->Time2)?" and ".$lists[0]->Time2:""));
        $sheet->setCellValue('B17', $lists[0]->course_title);
        $sheet->setCellValue('B18', $lists[0]->student_year);
        $sheet->setCellValue('B19', $lists[0]->section);

        $sheet->setCellValue('B21', ucwords(strtolower(utf8_decode($lists[0]->empFirstName . (empty($lists[0]->empMiddleName)?" ":" ".$lists[0]->empMiddleName[0].". ") .$lists[0]->empLastName))));

        $sheet->setCellValue('B24', date('F j, Y'));
        $sheet->setCellValue('B26', Crypt::encryptstring($this->id));

        $sheet = $event->writer->getSheetByIndex(1);
        $iteration = 8;
        foreach ($lists as $order) {

            $D = "D".($iteration);
            $E = "E".($iteration);
            $F = "F".($iteration);
            $G = "G".($iteration);
            $H = "H".($iteration);
            $I = "I".($iteration);
            $J = "J".($iteration);

            $sheet->setCellValue($D, $order->StudentNo);
            $sheet->setCellValue($E, ucwords(strtolower(utf8_decode($order->LastName))));
            $sheet->setCellValue($F, ucwords(strtolower(utf8_decode($order->FirstName))));
            $sheet->setCellValue($G, (empty($order->MiddleName)?"":$order->MiddleName[0]));
            $sheet->setCellValue($H, $order->accro);
            $sheet->setCellValue($I, $order->StudentYear);
            $sheet->setCellValue($J, (strlen($order->Section)==1?strtoupper($order->Section):$order->Section));

            $iteration++;
        }

    }
}

?>
