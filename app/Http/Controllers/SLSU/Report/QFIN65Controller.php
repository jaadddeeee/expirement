<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use GENERAL;
use Preference;
use App\Models\Enrolled;

class QFIN65Controller extends TCPDF
{
    protected $letter;
    protected $id;
    protected $sy;
    protected $sem;
    protected $data;
    protected $width;
    protected $cashier;
    protected $fee = 0;

    public function __construct(){
        $this->letter = new LetterHead();
        $this->width = array(5, 20, 50, 30, 40, 40);
    }

    private function getData(){
        $id = $this::getId();

        // dd($id);
        $cc = "courseoffering".$this::getSy().$this::getSem();
        $enrolled = new Enrolled();

        $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
          'c.accro','r.StudentYear','r.Section','r.SchoolLevel','s.StudentNo',
          's.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
          'm.course_major','cc.isWaive',
          'sc1.tym as Time1', 'sc2.tym as Time2',
          'e.LastName as empLastName', 'e.FirstName as empFirstName', 'e.MiddleName as empMiddleName', 'e.EmploymentStatus')
          ->leftjoin($cc." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
          ->leftjoin("schedule_time as sc1", "cc.sched", "=", "sc1.id")
          ->leftjoin("schedule_time as sc2", "cc.sched2", "=", "sc2.id")
          ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
          ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
          ->leftjoin("course as c", "r.Course", "=", "c.id")
          ->leftjoin("major as m", "r.Major", "=", "m.id")
          ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
          ->leftjoin("transcript as t", $enrolled->getTable().".sched", "=", "t.id")
          ->where("cc.coursecode", $id)
          ->where("r.SchoolYear", session('schoolyear'))
          ->where("r.Semester", session("semester"))
          ->orderBy("s.LastName")
          ->orderBy("s.FirstName")
          ->get();

        return $lists;
    }

    public function Header(){

      $this->data = $this::getData();

      $this->letter->ReportHeader();

			$startY = 40;
      $this::setXY(17, $startY);

			$this::SetFont('cambriab','',10);
			$term = GENERAL::Semesters()[$this::getSem()]['Long'] . " AY " . GENERAL::setSchoolYearLabel($this::getSy(),$this::getSem());

			$this::Cell(180,4,$term,0,0,'C');

			$startY += 5;
			$this::setXY(17, $startY);
			$this::SetFont('cambriab','',10);
			$this::Cell(180,5,"ASSESSED FEE OF APPROVED PETITIONED SUBJECT",0,0,'C');

			$this::SetFont('cambriab','',8);

			$startY += 10;
			$this::setXY(20, $startY);
			$this::SetFont('cambria','',9);
			$this::Cell(180,4,"Name of Requested Subject:",0,0,'L');

      $startX = 60;
      $startY += 4;
      $this::Line($startX, $startY, $startX+=30, $startY);

      $tmpX = 60;
			$this::setXY($tmpX, $startY-4);
      $this::Cell(30,4,$this->data[0]->coursecode,0,0,'C');

      $startX += 5;
      $this::Line($startX, $startY, $startX+=80, $startY);

      $tmpX += 35;
      $this::setXY($tmpX, $startY-4);
      $this::Cell(80,4,$this->data[0]->coursetitle,0,0,'C');

      $startX += 5;
      $this::Line($startX, $startY, $startX+=20, $startY);

      $tmpX += 85;
      $this::setXY($tmpX, $startY-4);
      $this::Cell(20,4,$this->data[0]->units,0,0,'C');

      // $startY += 4;
      $tmpX = 20;
      $this::setXY($tmpX, $startY);
      $chkLec = " ";
      $chkLab = " ";
      if ($this->data[0]->lec > 0)
      $chkLec = "x";

      if ($this->data[0]->lab > 0)
      $chkLab = "x";

      $this::Cell(30,4,"(".$chkLec.") Lecture    (".$chkLab.") Lab",0,0,'L');

      $tmpX = 60;
      $this::setXY($tmpX, $startY);
      $this::Cell(30,4,"Course Code",0,0,'C');
      $tmpX += 35;
      $this::setXY($tmpX, $startY);
      $this::Cell(80,4,"Descriptive Title",0,0,'C');
      $tmpX += 85;
      $this::setXY($tmpX, $startY);
      $this::Cell(20,4,"Unit",0,0,'C');

			$startY += 10;
			$this::setXY(20, $startY);
			$this::Write(3, "Subject Schedule:");

      $this::setXY(108, $startY);
			$this::Write(3, "Name of Subject Instructor:");

      $startX = 45;
      $startY += 4;
      $this::Line($startX, $startY, $startX+60, $startY);

      $tmpX = 45;
			$this::setXY($tmpX, $startY-4);
			$this::Cell(60,4,(empty($this->data[0]->sched)?"TBA":$this->data[0]->Time1) . (empty($this->data[0]->sched2)?"":" & ".$this->data[0]->Time2) ,0,0,'C');


      $startX = 148;
      $this::Line($startX, $startY, $startX+52, $startY);

			$tmpX += 90;
			$this::setXY($tmpX, $startY-4);
			$this::Cell(52,4,$this->data[0]->empFirstName.(empty($this->data[0]->empMiddleName)?" ":" ".$this->data[0]->empMiddleName[0].". ").$this->data[0]->empLastName,0,0,'C');

      $startX = 45;
      $this::setXY($startX, $startY);
      $this::Cell(60,3,"(Room, Day, Time)",0,0,'C');

			// $this::SetFont('cambria','',7);
			// $this::Line($startX, $startY, 200, $startY);

			$startY +=10;
      $startX = 15;

      // $this::Line($startX, $startY-.5, $startX , $startY+5);

			$this::Line($startX, $startY-.5, 200, $startY-.5);

			$this::setXY($startX, $startY-.5);
			$this::MultiCell($this->width[0],2,"No",0,'C');
			$this::Line($startX+5, $startY-.6, $startX+$this->width[0], $startY+5);

      $startX += 5;
      $this::setXY($startX, $startY-.5);
			$this::MultiCell($this->width[1],2,"StudentNo",0,'C');
			$this::Line($startX+$this->width[1], $startY-.6, $startX+$this->width[1], $startY+5);

      $startX += $this->width[1];
      $this::setXY($startX, $startY-.5);
			$this::MultiCell($this->width[2],2,"Name of Requestor",0,'C');
			$this::Line($startX+$this->width[2], $startY-.6, $startX+$this->width[2], $startY+5);

      $startX += $this->width[2];
      $this::setXY($startX, $startY-.5);
			$this::MultiCell($this->width[3],2,"Course & Year",0,'C');
			$this::Line($startX+$this->width[3], $startY-.6, $startX+$this->width[3], $startY+5);

      $startX += $this->width[3];
      $this::setXY($startX, $startY-.5);
			$this::MultiCell($this->width[4],2,"Major/ Specialization",0,'C');
			$this::Line($startX+$this->width[4], $startY-.6, $startX+$this->width[4], $startY+5);

      $startX += $this->width[4];
      $this::setXY($startX, $startY-.5);
			$this::MultiCell($this->width[5],2,"Assessed Fee per Student",0,'C');
			// $this::Line($startX+$this->width[5], $startY-.6, $startX+$this->width[5], $startY+5);


			$startY += 5;
			$this::setXy($startX, $startY);
			$this::Line(15, $startY, 200, $startY);

      $this::setXy(15, 120);
    }

    public function Footer(){
      $startY = 247;
      $this::SetFont('cambria','',9);

			$startX = 17;
			$startY += 5;
			$this::SetFont('cambria','',8);
			$this::setXY($startX, $startY);
			$this::Cell(55, 4, "Prepared by:",0,0,"L");

      $startX = 80;
			$this::setXY($startX, $startY);
			$this::Cell(55, 4, "Certified by:",0,0,"L");

      $startX += 60;
			$this::setXY($startX, $startY);
			$this::Cell(55, 4, "Assessed Fee Encoded by:",0,0,"L");

			$startX = 17;
			$startY += 10;
			$this::setXY($startX, $startY);
			$this::SetFont('cambria','',7);
			$this::Cell(50, 3, "Name and Signature of Accounting Staff","T",0,"L");
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(50, 3, "Date: _________________________",0, 0,"L");

      $startX = 80;
      $startY -= 3;
			$this::setXY($startX, $startY);
			$this::SetFont('cambria','',7);
			$this::Cell(50, 3, "Name and Signature of Accountant","T",0,"L");
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(50, 3, "Date: _________________________",0, 0,"L");

      $startX += 60;
      $startY -= 3;
			$this::setXY($startX, $startY);
			$this::SetFont('cambria','',7);
			$this::Cell(50, 3, "Name and Signature of Cashier's Staff","T",0,"L");
			$startY += 3;
			$this::setXY($startX, $startY);
			$this::Cell(50, 3, "Date: _________________________",0, 0,"L");



      $this->letter->ReportFooter(['QC' => config('QC.IN65')]);
    }

    function List()
		{

        $clsprefs = new Preference();
        $prefs = $clsprefs->GetDefaults(['RequestAmountPT','RequestAmountRG']);

			  $this::SetFont('cambria','',10);
		    $this::setXy(15, 88);
		    // Data
		    $ctr = 1;
        $ctrbreak = 1;
        $divisor = count($this->data);
        $honorariumpt = $clsprefs->GetDefaultValue($prefs, "RequestAmountPT");
        $honorariumrg = $clsprefs->GetDefaultValue($prefs, "RequestAmountRG");

        $job = "pt";
        if (!empty($this->data[0]->EmploymentStatus)){
            if (strtolower($this->data[0]->EmploymentStatus) == "permanent - faculty"){
              $job = "reg";
            }

            if (strtolower($this->data[0]->EmploymentStatus) == "temporary - faculty"){
              $job = "reg";
            }
        }


        if ($job == 'pt'){
          if (empty($honorariumpt)){
            $feeperstudent = 0;
          }else{
            $lab = (empty($this->data[0]->lab)?0:$this->data[0]->lab);
            $lec = (empty($this->data[0]->lec)?0:$this->data[0]->lec);
            $hours = ($lab * 3) + $lec ;
            $feeperstudent = (($honorariumpt * $hours) * 18) / $divisor;
          }
        }else{
          if (empty($honorariumrg)){
            $feeperstudent = 0;
          }else{
            $lab = (empty($this->data[0]->lab)?0:$this->data[0]->lab);
            $lec = (empty($this->data[0]->lec)?0:$this->data[0]->lec);
            $hours = ($lab * 3) + $lec;
            $feeperstudent = (($honorariumrg * $hours) * 18) / $divisor;
          }
        }


        $this->setFee($feeperstudent);

		    foreach($this->data as $key)
		    {
		    	if (strtolower(session('campus')) == "mcc"){
            $name = mb_strtoupper(utf8_decode($key->LastName)).', '.
              mb_strtoupper(utf8_decode($key->FirstName)).
              (empty($key->MiddleName)?"":" ".$key->MiddleName[0].".");
          }else{
            $name = ucwords(strtolower(utf8_decode($key->LastName))).", ".
            ucwords(strtolower(utf8_decode($key->FirstName))).
              (empty($key->MiddleName)?"":" ".$key->MiddleName[0].".");
          }

          $feeB = number_format($feeperstudent,2);
          if ($key->isWaive == 1){
            $feeB = "WAIVED";
          }

          $interval = 5;
          $this::SetFont('cambria','',9);
          $this::Cell($this->width[0],$interval,str_pad($ctr, 2, "0", STR_PAD_LEFT),'B',0,'C');
          $this::SetFont('cambria','',10);
          $this::Cell($this->width[1],$interval,$key->StudentNo,'LB');
          $this::Cell($this->width[2],$interval,$name,'LB',0,'L');
          $this::Cell($this->width[3],$interval,utf8_decode($key->accro).' '.$key->StudentYear,'LB',0,'L');
          $this::Cell($this->width[4],$interval,substr($key->course_major, 0, 21),'LB',0,'L');
          $this::Cell($this->width[5],$interval,$feeB."  ",'LB',0,'R');
          $this::SetFont('cambria','',9);
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
		    $this::Cell(190,$interval,str_repeat("*", 50)."nothing follows".str_repeat("*", 50),0,0,'C');
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

    /**
     * Get the value of cashier
     */
    public function getCashier()
    {
        return $this->cashier;
    }

    /**
     * Set the value of cashier
     *
     * @return  self
     */
    public function setCashier($cashier)
    {
        $this->cashier = $cashier;

        return $this;
    }

    /**
     * Get the value of fee
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set the value of fee
     *
     * @return  self
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }
}
