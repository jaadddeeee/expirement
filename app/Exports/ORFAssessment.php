<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;

use GENERAL;

class ORFAssessment extends TCPDF
{

  protected $id;
  protected $sy;
  protected $sem;
  protected $reg;
  protected $one;
  protected $lastY;

  protected $laboratories = [];
  protected $units = 0;
  protected $rateperunit = 0;
  protected $computestyle = '';
  protected $Scholarship = [];
  protected $AmountDue = 0;
  protected $name;
  public function __construct(){
    $this->letter = new LetterHead();
    $this->cambria = 'cambria';
    $this->cambriabold = 'cambriab';
    $this->deffontsize = 9;
  }

  private function generate(){
    $this->reg = Registration::where("RegistrationID", $this->getId())->first();
    $this->one = Student::where("StudentNo", $this->reg->StudentNo)->first();

    $this->setName(\Str::slug($this->one->LastName.'-'.$this->one->FirstName));

    $programlevel = DB::connection(strtolower(session('campus')))->table("studentlevel")->where("Description", $this->reg->SchoolLevel)->first();
    $this->computestyle = $programlevel->Style;
    $this->rateperunit = (empty($this->reg->UnitCost)?0:str_replace(",","",$this->reg->UnitCost));

    $sch = [];

    if (!empty($this->reg->Scholar)){
        $sch = DB::connection(strtolower(session('campus')))->table("scholar")
            ->where("id", $this->reg->Scholar)->first();
    }
    $this->Scholarship = $sch;
  }

  public function Header(){
    $this->generate();
    $this->letter->ReportHeader();
    $startY = 36;

    $this::setXY(13, $startY);

    $this::SetFont('cambriab','',9);
    $this::MultiCell(70,4,"STUDENT'S OFFICIAL REGISTRATION FORM\nand ASSESSMENT SLIP",0,'C');

    if ($this->reg->finalize == 1 and $this->reg->TES == 1){
      $this::SetFillColor(255,255,255);
      $this::Rect(135,$startY,$startY+15,15);
      $this::setXY(130,$startY);
      $this::SetFont('cambriab','',8);
      $this::Cell(60,5,"AVAILED FREE HE",0,0,"C");
      $this::SetFont('cambria','',7);
      $this::setXY(130, $startY+4);
      $this::MultiCell(60,3,"(Tuition and Other School Fees)\nRA 10931 (Universal Access to Quality\nTertiary Education Act of 2017)",0, "C");
    }

    $startY += 15;
    $startX = 14;
    $this::SetFont('cambriab','',9);
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"OFFICIAL REGISTRATION FORM",0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY(120, $startY);
    $this::Cell(30,5,"Academic Year: ".GENERAL::setSchoolYearLabel($this->reg->SchoolYear,$this->reg->Semester,session('campus'))
                    ."     Semester: ".GENERAL::Semesters()[$this->reg->Semester]['Short'],0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $startY += 5;
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Student No",0,0,'L');

    $this::SetFont($this->cambriabold,'',10);
    $this::setXY($startX+25, $startY);
    $this::Cell(30,5,": ".$this->one->StudentNo,0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX+50, $startY);
    $this::Cell(30,5,"Year/Section: ".(empty($this->reg->StudentYear)?"":$this->reg->StudentYear).(empty($this->reg->Section)?"":"/".$this->reg->Section),0,0,'L');

    $this::setXY($startX+90, $startY);
    $this::Cell(30,5,"Status",0,0,'L');

    $this::setXY(120, $startY);
    $this::Cell(30,5,": ".$this->reg->StudentStatus,0,0,'L');

    $this::setXY(150, $startY);
    $this::Cell(30,5,"Nationality",0,0,'L');

    $this::setXY(180, $startY);
    $this::Cell(30,5,": ".$this->one->nationality,0,0,'L');

    //2nd line
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $startY += 4;
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Name",0,0,'L');

    $this::SetFont($this->cambriabold,'',11);
    $this::setXY($startX+25, $startY);
    $this::Cell(30,5,": ".mb_strtoupper(utf8_decode($this->one['FirstName']) .(empty($this->one['MiddleName'])?" ":" ".utf8_decode($this->one['MiddleName'])).' '.utf8_decode($this->one['LastName'])),0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX+90, $startY);
    $this::Cell(30,5,"Sex",0,0,'L');

    $this::setXY(120, $startY);
    $this::Cell(30,5,": ".GENERAL::Gender()[$this->one->Sex],0,0,'L');

    $this::setXY(150, $startY);
    $this::Cell(30,5,"Civil Status",0,0,'L');

    $this::setXY(180, $startY);
    $this::Cell(30,5,": ".$this->one->civil_status,0,0,'L');

    //3rd line
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $startY += 4;
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Course",0,0,'L');

    $this::setXY($startX+25, $startY);
    $this::Cell(30,5,": ".$this->reg->course->accro ." (".$this->reg->cur_num.")",0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX+90, $startY);
    $bd = explode(" ",$this->one['BirthDate']);
    $this::Cell(30,5,"Birth Date",0,0,'L');

    $this::setXY(120, $startY);
    $this::Cell(30,5,": ".GENERAL::numtoMonths($bd[0])." ".$bd[1].", ".$bd[2],0,0,'L');

    //4th line
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $startY += 4;
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Spl/Major",0,0,'L');

    $this::setXY($startX+25, $startY);
    $this::Cell(30,5,": ".(empty($this->reg->major->course_major)?"NONE":$this->reg->major->course_major),0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX+90, $startY);

    $this::Cell(30,5,"Birth Place",0,0,'L');

    $this::setXY(120, $startY);
    $this::Cell(30,5,": ".utf8_decode($this->one['BirthPlace']),0,0,'L');
    $this->setLastY($startY);

  }

  public function Footer(){
    // $startY = $this->getLastY();
    $startY = 260;
    $startX = 15;
    $this::SetFont($this->cambria,'',7.5);
    $this::setY($startY);
    $this::setX($startX);
    $this::MultiCell(0, 3, "Note: Congratulations! You are officially enrolled.\nThese ORF and Assessment Slip are system validated, hence manual stamping of validation and signatures of Registrar, Student and Cashier are not evident.\nDate validated: ".$this->reg->DateValidated." - ".$this->reg->ValidatedBy,0,"L");
    $this->letter->ReportFooter(['QC' => config('QC.RE11')]);
  }

  public function List()
  {

    $tblCC = "courseoffering".$this->reg->SchoolYear.$this->reg->Semester;
    $tblGrades = "grades".$this->reg->SchoolYear.$this->reg->Semester;
    $subjects = DB::connection(strtolower(session('campus')))->table($tblGrades." as g")
        ->select("t.courseno","t.coursetitle","t.units","t.exempt",
                "cc.coursecode", "cc.Scheme", "st1.tym as tym1", "st2.tym as tym2", "e.FirstName", "e.LastName",
                "cc.fee")
        ->leftjoin($tblCC." as cc", "g.courseofferingid", "=", "cc.id")
        ->leftjoin("transcript as t", "g.sched", "=", "t.id")
        ->leftjoin("schedule_time as st1", "cc.sched", "=", "st1.id")
        ->leftjoin("schedule_time as st2", "cc.sched2", "=", "st2.id")
        ->leftjoin("employees as e", "cc.teacher", "=","e.id")
        ->where("g.gradesid", $this->reg->RegistrationID)
        ->orderby("t.sort_order", "ASC")
        ->get();

    $header = array('CODE', 'COURSE NO', 'COURSE TITLE', 'UNITS', 'SCHEDULE', 'TEACHER');
    $w = array(20, 25, 55, 10, 45,30);
    $units = 0;
    $this::SetFont($this->cambria,'',8);
    $startY = $this->getLastY()+7;
    $this::setXY(14,$startY);
    for($i=0;$i<count($header);$i++)
    {
      $this::Cell($w[$i],5,$header[$i],1,0,'C');
    }

    $this::Ln();

    $startX = 14;
    $startY += 5;
    $this::setY($startY);
    $ctr = 0;
    foreach($subjects as $row)
    {

        $this::setX($startX);
        set_time_limit(0);
        if ($row->exempt != 1){
            $units += $row->units;
        }

        //for laboratory fees
        if ($row->fee > 0){
            array_push($this->laboratories, ['Subject'=>$row->courseno,'Fee'=>$row->fee]);
        }

        $tym = $row->tym1;
        $ctr++;
        $this::Cell($w[0],4,$row->coursecode,'L');
        $this::Cell($w[1],4,$row->courseno,'');
        $this::Cell($w[2],4,substr($row->coursetitle,0,40),'');
        $this::Cell($w[3],4,($row->exempt == 1?"(".$row->units.")":$row->units),'','','C');
        $this::Cell($w[4],4,(empty($row->Scheme)?"":"T".$row->Scheme." ").$tym,'');
        $this::Cell($w[5],4,(empty($row->LastName)?"":$row->LastName.', '.$row->FirstName[0]),'R');
        $this::Ln();

        if (!empty($row->tym2)){
            $ctr++;
            $this::setX($startX);
            $this::Cell($w[0],4,"",'L');
            $this::Cell($w[1],4,"",'');
            $this::Cell($w[2],4,"",'');
            $this::Cell($w[3],4,"",'','','C');
            $this::Cell($w[4],4,$row->tym2,'');
            $this::Cell($w[5],4,"",'R');
            $this::Ln();
        }
    }

    $this::setX($startX);
    $this::SetFont($this->cambriabold,'',9);
    $this::Cell($w[0],4,"",'LB');
    $this::Cell($w[1],4,"",'B');
    $this::Cell($w[2],4,"TOTAL UNITS",'B','','R');
    $this::Cell($w[3],4,$units,'B','','C');
    $this::Cell($w[4],4,'','B');
    $this::Cell($w[5],4,"",'BR');
    $this::Ln();
    $this->units = $units;
    $this->setLastY($ctr * 5 + $startY);
  }

  public function Assessment(){
    $startY = $this->getLastY();
    $startX = 14;
    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"ASSESSMENT SLIP",0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
		$this::setXY(120, $startY);
		$this::Write(0,"Level: " . $this->reg->SchoolLevel);

    //1st line
    $startY+=5;
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Name",0,0,'L');

    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $this::setXY($startX+25, $startY);
    $this::Cell(30,5,": ".mb_strtoupper(utf8_decode($this->one->FirstName) .(empty($this->one->MiddleName)?" ":" ".utf8_decode($this->one->MiddleName)).' '.utf8_decode($this->one->LastName)),0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX+90, $startY);
    $this::Cell(30,5,"Year/Section",0,0,'L');

    $this::setXY(125, $startY);
    $this::Cell(30,5,": ".(empty($this->reg->StudentYear)?"":$this->reg->StudentYear).(empty($this->reg->Section)?"":"/".$this->reg->Section),0,0,'L');

    $this::setXY(150, $startY);
    $this::Cell(30,5,"Status",0,0,'L');

    $this::setXY(170, $startY);
    $this::Cell(30,5,": ".$this->reg->StudentStatus,0,0,'L');

    //2nd line

    $startY+=5;
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Course",0,0,'L');

    $this::setXY($startX+25, $startY);
    $this::Cell(30,5,": ".$this->reg->course->accro ." (".$this->reg->cur_num.")",0,0,'L');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::setXY($startX+60, $startY);
    $this::Cell(30,5,"Spl/Major",0,0,'L');

    $this::setXY($startX+80, $startY);
    $this::Cell(30,5,": ".(empty($this->reg->major->course_major)?"NONE":$this->reg->major->course_major),0,0,'L');

    $this::setXY(150, $startY);
    $this::Cell(30,5,"Period",0,0,'L');

    $this::setXY(170, $startY);
    $this::Cell(30,5,": ".GENERAL::setSchoolYearLabel($this->reg->SchoolYear,$this->reg->Semester,session('campus'))
    ." ".GENERAL::Semesters()[$this->reg->Semester]['Short'],0,0,'L');


    //TUITION
    $this->setLastY($startY);
    $startY = $startY+8;
    $startX = 120;
    $this::setXY($startX, $startY);
    $this::SetFont($this->cambriabold,'',$this->deffontsize);

    $out = "TUITION FEE (".$this->units .' x '.number_format($this->rateperunit, 2,'.',',').")";
    $tuition = $this->units*$this->rateperunit;
    if ($this->computestyle != "pUnit"){
        $out = "TUITION FEE";
        $tuition = $this->rateperunit;
    }
    $this::setX($startX);
    $this::Cell(50,3.5,$out,'');
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::Cell(20,3.5,(empty($tuition)?"0.00":number_format($tuition,2,'.',',')),'','','R');

    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $startY += 5;
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Less",0,0,'L');

    //compute scholar
    $sch = 0;
    $schname = 's';
    $finalTuition = 0;
    $minus = 0;
    if (!empty($this->Scholarship)){
        $minus = 0;
        $schname = "(".$this->Scholarship->scholar_name.")";
        if ($this->Scholarship->typ == 1){
            $minus = ($tuition * ($this->Scholarship->amount/100));
        }elseif ($this->Scholarship->typ == 2){
            $minus = (empty($this->Scholarship->amount)?0:str_replace(",","",$this->Scholarship->amount));
        }elseif ($this->Scholarship->typ == 3){
            $minus = 0;
        }
    }
    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $startY += 5;
    $this::setXY($startX, $startY);
    $this::Cell(50,5,"Disc/Scholarship".$schname,0,0,'L');
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::Cell(20,3.5,(empty($minus)?"0.00":number_format($minus,2,'.',',')),'','','R');

    $finalTuition = $tuition - $minus;
    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $startY += 5;
    $this::setXY($startX, $startY);
    $this::Cell(50,5,"SUB-TOTAL",0,0,'L');
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::Cell(20,3.5,(empty($finalTuition)?"0.00":number_format($finalTuition,2,'.',',')),'','','R');

    $this->AmountDue += (empty($finalTuition)?0:$finalTuition);


    $startY = $this->getLastY()+8;
    $startX = 14;
    $this::setXY($startX, $startY);
    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $this::Cell(30,5,"GENERAL FEES",0,0,'L');
    $this::SetFont($this->cambria,'',8);
    $tblAssess = "assessment".$this->reg->SchoolYear.$this->reg->Semester;
    $fees = DB::connection(strtolower(session('campus')))->table($tblAssess)
        ->where("registrationid", $this->reg->RegistrationID)
        ->get();

    $genFees = 0;
    $startY += 8;
    $this::setXY($startX, $startY);
    $this::SetFont($this->cambria,'',8);
    foreach ($fees as $key) {
        set_time_limit(0);
        $this::setX($startX+2);
        $this::Cell(60,3.5,$key->item,'');
        $this::Cell(20,3.5,(empty($key->amount)?"0.00":number_format($key->amount,2,'.',',')),'','','R');
        $genFees  += (empty($key->amount)?0:str_replace(",", "", $key->amount));
        $this::Ln();
    }
    // $this->setGeneralFees($genFees);
    $this::setX($startX+2);
    $this::SetFont($this->cambriabold,'',8);
    $this::Cell(60,3,"SUB-TOTAL",'');
    $this::Cell(20,3,number_format($genFees,2,'.',','),'','','R');
    $this->AmountDue += (empty($genFees)?0:$genFees);

    //FOR REQUESTED SUBJECT
    $requesteds = DB::connection(strtolower(session('campus')))->table('adjust as a')
        ->join("transcript as t", "a.CourseNo", "=", "t.id")
        ->where("a.StudentNo", $this->reg->StudentNo)
        ->where("a.SchoolYear",$this->reg->SchoolYear)
        ->where("a.Semester",$this->reg->Semester)
        ->get();

    $totalREQ = 0;
    $startY += 8;
    // dd($requesteds);
    if (count($requesteds)> 0){

        $startY += 20;
        $startX = 120;
        $this::SetFont($this->cambriabold,'',$this->deffontsize);
        $this::setXY($startX, $startY);
        $this::Cell(30,5,"REQUESTED SUBJECT(s)",0,0,'L');
        $startY += 4;
        $this::setXY($startX, $startY);
        $this::SetFont($this->cambria,'',$this->deffontsize);
        foreach($requesteds as $requested){
            $this::setX($startX+5);
            $this::Cell(45,3.5,$requested->courseno,'');
            $this::Cell(20,3.5,(empty($requested->Amount)?"0.00":number_format($requested->Amount,2,'.',',')),'','','R');
            $totalREQ  += (empty($requested->Amount)?0:str_replace(",", "", $requested->Amount));
            $this::Ln();
            $startY += 4;
        }
        $this::setXY($startX, $startY);
        $this::SetFont($this->cambriabold,'',8);
        $this::Cell(50,3,"SUB-TOTAL",'');
        $this::Cell(20,3,number_format($totalREQ,2,'.',','),'','','R');
        $this->AmountDue += (empty($totalREQ)?0:$totalREQ);
        $startY += 4;
    }else{
        $startY += 20;
    }
    //END REQUESTED
    //For Laboratories
    $labTotal = 0;
    $startX = 120;
    $this::SetFont($this->cambriabold,'',$this->deffontsize);
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"LABORATORY FEE(s)",0,0,'L');
    $startY += 4;
    $this::setXY($startX, $startY);
    $this::SetFont($this->cambria,'',8);
    if (!empty($this->laboratories)){
        foreach($this->laboratories as $lab){
            $this::setX($startX+5);
            $this::Cell(45,3.5,$lab['Subject'],'');
            $this::Cell(20,3.5,(empty($lab['Fee'])?"0.00":number_format($lab['Fee'],2,'.',',')),'','','R');
            $labTotal  += (empty($lab['Fee'])?0:str_replace(",", "", $lab['Fee']));
            $this::Ln();
            $startY += 4;
        }
    }
    $this::setX($startX);
    $this::SetFont($this->cambriabold,'',8);
    $this::Cell(50,3,"SUB-TOTAL",'');
    $this::Cell(20,3,number_format($labTotal,2,'.',','),'','','R');
    $this->AmountDue += (empty($labTotal)?0:$labTotal);
    $startY += 8;
    $this::setXY($startX, $startY);
    $this::SetFont($this->cambriabold,'',14);
    $this::Cell(50,3,"AMOUNT DUE",'');

    if (!empty($this->Scholarship)){
        if ($this->Scholarship->typ == 3)
            $this::Cell(20,3,"0.00",'','','R');
        else
            $this::Cell(20,3,number_format($this->AmountDue,2,'.',','),'','','R');
    }else{
        $this::Cell(20,3,number_format($this->AmountDue,2,'.',','),'','','R');
    }

    $this->setLastY($startY);

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
   * Get the value of lastY
   */
  public function getLastY()
  {
    return $this->lastY;
  }

  /**
   * Set the value of lastY
   *
   * @return  self
   */
  public function setLastY($lastY)
  {
    $this->lastY = $lastY;

    return $this;
  }

  /**
   * Get the value of name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the value of name
   *
   * @return  self
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }
}

?>
