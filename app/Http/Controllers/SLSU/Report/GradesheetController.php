<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Support\Facades\DB;
use GENERAL;
use App\Models\Enrolled;
use App\Models\Prospectos;
class GradesheetController extends TCPDF
{
    protected $letter;
    protected $id;
    protected $sy;
    protected $sem;
    protected $data;
    public function __construct(){
        $this->letter = new LetterHead();
    }

    private function getData(){
        $id = $this::getId();
        $cc = "courseoffering".session('schoolyear').session("semester");

        $schedinfo = DB::connection(strtolower(session('campus')))
          ->table($cc)
          ->where('id', $id)
          ->first();

        $this->subinfo = Prospectos::find($schedinfo->courseid);


        $enrolled = new Enrolled();
        // dd();
        $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
          'c.accro','r.StudentYear','r.Section','r.SchoolLevel','s.StudentNo',
          's.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
          'sc1.tym as Time1', 'sc2.tym as Time2',
          'e.LastName as empLastName', 'e.FirstName as empFirstName', 'e.MiddleName as empMiddleName')
          ->where("courseofferingid", $id)
          ->where("r.SchoolYear", session('schoolyear'))
          ->where("r.Semester", session("semester"))
          ->where("r.finalize", 1)
          ->leftjoin($cc." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
          ->leftjoin("schedule_time as sc1", "cc.sched", "=", "sc1.id")
          ->leftjoin("schedule_time as sc2", "cc.sched2", "=", "sc2.id")
          ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
          ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
          ->leftjoin("course as c", "r.Course", "=", "c.id")
          ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
          ->leftjoin("transcript as t", $enrolled->getTable().".sched", "=", "t.id")
          ->orderBy("s.LastName")
          ->orderBy("s.FirstName")
          ->get();

        return $lists;
    }

    public function Header(){

      $this->data = $this::getData();

      $this->letter->ReportHeader();

      $this::SetFillColor(255,255,255);
			$this::Rect(15,36,55,15);
			$this::setXY(17,36);
			$this::SetFont('cambriab','',7);
			$this::Cell(60,5,"IMPORTANT REMINDER",0,0,"L");
			$this::SetFont('cambria','',7);
			$this::setXY(19, 40);
			$this::MultiCell(50,3,"Grade Sheets shall be at the Office of the Registrar 12 calendar days after the last day of the final examination schedule.",0, "L");
			$startY = 47;

      if (strtolower(session('campus')) == "sg"){
        $registrar = strtoupper("Office of the University Registrar");
      }else{
        $registrar =  strtoupper("Office of the Registrar");
      }
      $this::setXY(17, $startY);

			$this::SetFont('cambriab','',11);
			$this::Cell(180,5,$registrar,0,0,'C');

			$startY += 10;
			$this::setXY(17, $startY);
			$this::SetFont('cambriab','',10);
			$this::Cell(180,5,"GRADE SHEET/S",0,0,'C');

			$startY += 10;
			$this::setXY(17, $startY);
			$this::SetFont('cambriab','',8);

      $term = GENERAL::Semesters()[$this::getSem()]['Long'] . " / " . GENERAL::setSchoolYearLabel($this::getSy(),$this::getSem());

			$this::Cell(180,4,$term,0,0,'C');
			$startY += 5;
			$this::Line(82, $startY, 210-80, $startY);

			$startY += 1;
			$this::setXY(17, $startY);
			$this::SetFont('cambria','',8);
			$this::Cell(180,4,"Term/Academic Year",0,0,'C');

			$startY += 5;
			$this::setXY(20, $startY);
			$this::SetFont('cambria','',9);
			$this::Cell(180,4,"SUBJECT/COURSE",0,0,'L');

			$startY += 5;
			$this::setXY(20, $startY);
			$this::Write(3, "OFFERED IN ");
			// $this::Cell(0,4, .$this::getSchoolLevel(),0,0,'L');
			$this::SetFont('cambriab','',9);
			$this::Write(3, strtoupper($this->data[0]->SchoolLevel));
			$this::SetFont('cambria','',9);
			$this::Write(3, " PROGRAM LEVEL");

			$startY += 5;
			$startX = 25;
			$tmpX = 25;
			$this::setXY($tmpX, $startY);
			$this::Cell(20,4,$this->data[0]->coursecode,0,0,'C');
			$tmpX += 25;
			$this::setX($tmpX);
			$this::Cell(30,4,$this->subinfo->courseno,0,0,'C');

			$tmpX += 35;
			$this::setX($tmpX);
			$this::Cell(80,4,$this->subinfo->coursetitle,0,0,'C');

			$tmpX += 85;
			$this::setX($tmpX);
			$this::Cell(10,4,$this->subinfo->units,0,0,'C');

			$tmpX += 15;
			$this::setX($tmpX);
			$this::Cell(10,4,(empty($this->subinfo->lab)?0:$this->subinfo->lab)*3+(empty($this->subinfo->lec)?0:$this->subinfo->lec),0,0,'C');

			$startY += 5;
			$this::Line($startX, $startY, $startX+=20, $startY);

			$startX += 5;
			$this::Line($startX, $startY, $startX+=30, $startY);

			$startX += 5;
			$this::Line($startX, $startY, $startX+=80, $startY);

			$startX += 5;
			$this::Line($startX, $startY, $startX+=10, $startY);

			$startX += 5;
			$this::Line($startX, $startY, $startX+=10, $startY);

			$this::SetFont('cambria','',8);
			$tmpX = 25;
			$startY += 1;
			$this::setXY($tmpX, $startY);
			$this::Cell(20,4,"CRSE CODE",0,0,'C');
			$tmpX += 25;
			$this::setX($tmpX);
			$this::Cell(30,4,"CRSE NUMBER",0,0,'C');

			$tmpX += 35;
			$this::setX($tmpX);
			$this::Cell(80,4,"DESCRIPTIVE TITLE",0,0,'C');

			$tmpX += 85;
			$this::setX($tmpX);
			$this::Cell(10,4,"UNIT",0,0,'C');

			$tmpX += 15;
			$this::setX($tmpX);
			$this::Cell(10,4,"CHR/WK",0,0,'C');

			$startX = 25;
			$startY += 7;
			$this::setXy($startX, $startY);
			$this::Cell(5,4,"LEC: ",0,0,'L');
			$startX += 10;
			$this::setXy($startX, $startY);
			$this::Cell(5,4,$this->subinfo->lec,0,0,'C');

			$tmpX = 45;
			$this::setXY($tmpX, $startY);
			$this::Cell(72,4,(empty($this->data[0]->sched)?"TBA":$this->data[0]->Time1) . (empty($this->data[0]->sched2)?"":" & ".$this->data[0]->Time2) ,0,0,'C');

			$tmpX += 77;
			$this::setXY($tmpX, $startY);
			$this::Cell(72,4,$this->data[0]->empFirstName.(empty($this->data[0]->empMiddleName)?" ":" ".$this->data[0]->empMiddleName[0].". ").$this->data[0]->empLastName,0,0,'C');

			$startY += 4;
			$this::Line($startX, $startY, $startX+=5, $startY);

			$startX += 5;
			$this::Line($startX, $startY, $startX+=72, $startY);

			$startX += 5;
			$this::Line($startX, $startY, $startX+=72, $startY);


			$tmpX = 45;
			$startY += 1;
			$this::setXY($tmpX, $startY);
			$this::Cell(72,4,"CLASS SCHEDULE",0,0,'C');
			$tmpX += 77;
			$this::setX($tmpX);
			$this::Cell(72,4,"INSTRUCTOR/PROFESSOR",0,0,'C');

			$startX = 25;
			$startY += -1;
			$this::setXy($startX, $startY);
			$this::Cell(5,4,"LAB: ",0,0,'L');
			$startX += 10;
			$this::setXy($startX, $startY);
			$this::Cell(5,4,$this->subinfo->lab,0,0,'C');
			$startY += 3.5;
			$this::Line($startX, $startY, $startX+=5, $startY);

			$startX = 15;
			$startY += 5;
			$this::setXy($startX, $startY);

			$this::SetFont('cambria','',7);
			$this::Line($startX, $startY, 200, $startY);

			$startY +=.6;

			$this::setXY($startX, $startY-.5);
			$this::MultiCell(5,2,"SQ\nNo",0,'C');
			$this::Line($startX+5, $startY-.6, $startX+5, $startY+6);

			$startX += 5;
			$this::setXY($startX, $startY-.5);
			$this::MultiCell(20,2,"STUDENT\nNo.",0,'C');
			$this::Line($startX+20, $startY-.6, $startX+20, $startY+6);

			$startX += 20;
			$startY +=.9;
			$this::setXY($startX, $startY);
			$this::MultiCell(30,2,"SURNAME",0,'C');
			$this::Line($startX+30, $startY-1.4, $startX+30, $startY+6);

			$startX += 30;
			$this::setXY($startX, $startY);
			$this::MultiCell(30,2,"FIRST NAME",0,'C');
			$this::Line($startX+30, $startY-1.4, $startX+30, $startY+6);

			$startX += 30;
			$this::setXY($startX, $startY);
			$this::MultiCell(30,2,"MIDDLE NAME",0,'C');
			$this::Line($startX+30, $startY-1.4, $startX+30, $startY+6);

			$startX += 30;
			$this::setXY($startX, $startY);
			$this::MultiCell(25,2,"CRSE YEAR SECT",0,'C');
			$this::Line($startX+25, $startY-1.4, $startX+25, $startY+6);

			$startX += 25;
			$this::setXY($startX, $startY);
			$this::MultiCell(7,2,"MT",0,'C');
			$this::Line($startX+7, $startY-1.4, $startX+7, $startY+6);

			$startX += 7;
			$this::setXY($startX, $startY);
			$this::MultiCell(7,2,"FT",0,'C');
			$this::Line($startX+7, $startY-1.4, $startX+7, $startY+6);

			$startX += 7;
			$this::setXY($startX, $startY);
			$this::MultiCell(7,2,"AGR",0,'C');
			$this::Line($startX+7, $startY-1.4, $startX+7, $startY+6);

			$startX += 7;
			$this::setXY($startX, $startY);
			$this::MultiCell(19,2,"REMARK",0,'C');
			$this::Line($startX+19, $startY-1.4, $startX+19, $startY+6);

			$startX += 19;
			$this::setXY($startX, $startY - 1.4);
			$this::MultiCell(5,2,"SQ\nNo",0,'C');

			$startY += 5;
			$this::setXy($startX, $startY);
			$this::Line(15, $startY, 200, $startY);

      $this::setXy(15, 120);
    }

    public function Footer(){
      $startY = 217;
      $this::SetFont('cambria','',9);
			$startY += 3;
			$this::setXY(16, $startY);

			$this::Cell(30, 4, "MT - MID TERM",0,0,"L");
			$this::Cell(30, 4, "FT - FINAL TERM",0,0,"L");
			$this::Cell(60, 4, "AGR - AVERAGE GRADE/RATING",0,0,"L");
			$this::Cell(70, 4, "Page ".$this::PageNo()." / ".$this::getAliasNbPages() ,0,0,"R");
			$startY += 5;
			$this::setXY(16, $startY);
			$this::Write(3, "GRADING SYSTEM");
			$this::SetFont('cambria','',6);
			$startY += 4;
			$startX = 20;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"1.0 (98-100)\n1.1 (96-97)\n1.2 (93-95)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"1.3 (92)\n1.4 (91)\n1.5 (90)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"1.6 (89)\n1.7 (88)\n1.8 (87)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"1.9 (86)\n2.0 (85)\n2.1 (84)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"2.2 (83)\n2.3 (82)\n2.4 (81)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"2.5 (80)\n2.6 (79)\n2.7 (78)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"2.8 (77)\n2.9 (76)\n3.0 (75)", 0,"L");

			$startX += 17;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"3.1-3.5 (74-70) - Conditional\n5.0 (69 and Below) - Failed", 0,"L");

			$startX += 35;
			$this::setXY($startX, $startY);
			$this::multiCell(0,2,"INP - In Progress\nINC - Incomplete\nDR - Dropped", 0,"L");

			$startX = 20;
			$startY += 10;
			$this::SetFont('cambria','',8);
			$this::setXY($startX, $startY);
			$this::Cell(55, 4, "PREPARED/ENCODED AND SUBMITTED",0,0,"L");
			$this::Cell(10, 4, "Date:",0,0,"L");

			$startX = 75;
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(5, 4, "MT",0,0,"L");
			$this::Cell(5, 4, "__________",0,0,"L");
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(5, 4, "FT:",0,0,"L");
			$this::Cell(5, 4, "__________",0,0,"L");

			$startX = 21;
			$startY += 5;
			$this::setXY($startX, $startY);
			$this::Cell(50, 4, "Instructor's/Professor's Signature","T",0,"L");

			$startX = 20;
			$startY += 5;
			$this::SetFont('cambria','',8);
			$this::setXY($startX, $startY);
			$this::Cell(55, 4, "CHECKED AND VERIFIED",0,0,"L");
			$this::Cell(10, 4, "Date:",0,0,"L");

			$startX = 75;
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(5, 4, "MT",0,0,"L");
			$this::Cell(5, 4, "__________",0,0,"L");
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(5, 4, "FT:",0,0,"L");
			$this::Cell(5, 4, "__________",0,0,"L");

			$startX = 21;
			$startY += 5;
			$this::setXY($startX, $startY);
			$this::SetFont('cambria','',7);
			$this::Cell(50, 3, "Dep't Head / College Dean / Director /","T",0,"L");
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(50, 3, "Authorized Representative Signature over Printed Name",0, 0,"L");

			$startX = 120;
			$startY -= 15;
			$this::SetFont('cambria','',8);
			$this::setXY($startX, $startY);
			$this::Cell(55, 4, "RECEIVED / RECORDED",0,0,"L");

			$startY += 5;
			$startX = 175;
			$this::setXY($startX, $startY);
			$this::Cell(10, 4, "Date: __________",0,0,"L");

			$startX = 121;
			$startY += 7;
			$this::setXY($startX, $startY);
			$this::SetFont('cambria','',7);
			$this::MultiCell(50, 3, "Registrar / Authorized Representative\nSignature over Printed Name","T","L");

      $this->letter->ReportFooter(['QC' => config('QC.GradeSheet')]);
    }

    function List()
		{

			  $this::SetFont('cambria','',10);
		    $this::setXy(15, 120);
		    $w = array(5, 20, 30, 30, 30, 25, 7, 7, 7,19, 5);
		    // Header

		    // Data
		    $ctr = 1;
        $ctrbreak = 1;
		    foreach($this->data as $key)
		    {
		    	if (strtolower(session('campus')) == "mcc"){
            $fname = mb_strtoupper(utf8_decode($key->FirstName));
            $lname = mb_strtoupper(utf8_decode($key->LastName));
            $mname = mb_strtoupper(utf8_decode($key->MiddleName));
          }else{
            $fname = ucwords(strtolower(utf8_decode($key->FirstName)));
            $lname = ucwords(strtolower(utf8_decode($key->LastName)));
            $mname = ucwords(strtolower(utf8_decode($key->MiddleName)));
          }

          $interval = 5;
          $this::SetFont('cambria','',9);
          $this::Cell($w[0],$interval,str_pad($ctr, 2, "0", STR_PAD_LEFT),'B',0,'C');
          $this::SetFont('cambria','',10);
          $this::Cell($w[1],$interval,$key->StudentNo,'LB');
          $this::Cell($w[2],$interval,$lname,'LB',0,'L');
          $this::Cell($w[3],$interval,$fname,'LB',0,'L');
          $this::Cell($w[4],$interval,$mname,'LB',0,'L');
          $this::Cell($w[5],$interval,utf8_decode($key->accro).' '.$key->StudentYear.(empty($key->Section)?"":"-".(strlen($key->Section)==1?strtoupper($key->Section):$key->Section)),'LB',0,'L');
          $this::SetFont('cambria','',9);
          $this::Cell($w[6],$interval,(!empty($key->midterm)?GENERAL::GradeRemarks($key->midterm, 0):""),'LB',0,'C');
          $this::Cell($w[7],$interval,(!empty($key->finalterm)?GENERAL::GradeRemarks($key->finalterm, 0):""),'LB',0,'C');
          $this::Cell($w[8],$interval,(!empty($key->final)?GENERAL::GradeRemarks($key->final, 0):""),'LB',0,'C');
          $this::SetFont('cambria','',8);
          $this::Cell($w[9],$interval,GENERAL::GradeRemarksString($key->final),'LB',0,'');
          $this::SetFont('cambria','',9);
          $this::Cell($w[10],$interval,str_pad($ctr, 2, "0", STR_PAD_LEFT),'LB',0,'C');
          $this::Ln();
          $this::setX(15);
          // if ($ctrbreak >= 20){
          //   $this::AddPage();
          //   $this::setXy(15, 120);
          //   $ctrbreak = 0;
          // }
          $ctrbreak++;
          $ctr++;
		    }

		    $this::setX(14);
		    $this::Cell(190,$interval,"*****************************************************************************************************************************",0,0,'C');
		}


    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of sy
     */
    public function getSy()
    {
        return $this->sy;
    }

    /**
     * Set the value of sy
     *
     * @return  self
     */
    public function setSy($sy)
    {
        $this->sy = $sy;

        return $this;
    }

    /**
     * Get the value of sem
     */
    public function getSem()
    {
        return $this->sem;
    }

    /**
     * Set the value of sem
     *
     * @return  self
     */
    public function setSem($sem)
    {
        $this->sem = $sem;

        return $this;
    }
}
