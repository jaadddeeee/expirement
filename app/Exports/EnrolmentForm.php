<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;

use GENERAL;

class EnrolmentForm extends TCPDF
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
    $this->reg = Registration::select('registration.*', DB::connection(strtolower(session('campus')))->raw("CONCAT(ev.FirstName, ' ',
           IF(ev.MiddleName != '' AND ev.MiddleName IS NOT NULL, CONCAT(LEFT(ev.MiddleName, 1), '.'), ''),
           ' ', ev.LastName) as ValidName"),
        DB::connection(strtolower(session('campus')))->raw("CONCAT(eu.FirstName, ' ',
           IF(eu.MiddleName != '' AND eu.MiddleName IS NOT NULL, CONCAT(LEFT(eu.MiddleName, 1), '.'), ''),
           ' ', eu.LastName) as EnrolName"))
      ->leftjoin('accountsuser as auv', 'registration.ValidatedBy', '=','auv.UserName')
      ->leftjoin('employees as ev', 'auv.Emp_No','=','ev.id')
      ->leftjoin('accountsuser as aue', 'registration.EnrollingOfficer', '=','aue.UserName')
      ->leftjoin('employees as eu', 'aue.Emp_No','=','eu.id')
      ->where("RegistrationID", $this->getId())
      ->first();
    $this->one = Student::where("StudentNo", $this->reg->StudentNo)->first();

    $this->setName(\Str::slug($this->one->LastName.'-'.$this->one->FirstName));
  }

  public function Header(){
    $this->generate();
    $this->letter->ReportHeader();
    $startY = 36;
    $startY += 8;
    $this::setXY(13, $startY);

    $this::SetFont('cambriab','',12);
    $this::MultiCell(180,4,"ENROLLMENT FORM",0,'C');

    $startY += 10;
    $startX = 28;
    $this::setXY($startX, $startY);
    $this::SetFont('cambria','',10);
    $this::Cell(30,4,"Student Type:",0,0,'L');

    $startY += 5;
    $startX = 30;

    if ($this->reg->StudentStatus == 'New'){
      $this::Rect($startX+5,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+5,$startY,4,4);
    }
    $this::setXY($startX + 10, $startY);
    $this::Cell(30,4,"New",0,0,'L');



    $startY += 5;
    if ($this->reg->StudentStatus == 'Transferee'){
      $this::Rect($startX+5,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+5,$startY,4,4);
    }
    $this::setXY($startX + 10, $startY);
    $this::Cell(30,4,"Transferee",0,0,'L');

    //StudentNo
    $this::setXY($startX + 90, $startY);
    $this::Cell(30,4,"Student ID Number: ".$this->reg->StudentNo,0,0,'L');


    $startY += 5;
    if ($this->reg->StudentStatus == 'Continuing'){
      $this::Rect($startX+5,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+5,$startY,4,4);
    }
    $this::setXY($startX + 10, $startY);
    $this::Cell(30,4,"Continuing",0,0,'L');

    //StudentNo
    $this::setXY($startX + 90, $startY);
    $this::Cell(30,4,"Academic Year: ".GENERAL::setSchoolYearLabel($this->reg->SchoolYear,$this->reg->Semester,session('campus')),0,0,'L');


    $startY += 5;
    $this::setXY($startX+5, $startY);
    $this::Cell(30,4,"____ graduating ____ non-graduating",0,0,'L');

    if ($this->reg->Semester == 1){
      $this::Rect($startX+90,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+90,$startY,4,4);
    }
    $this::setXY($startX + 95, $startY);
    $this::Cell(30,4,"1st Sem",0,0,'L');

    if ($this->reg->Semester == 2){
      $this::Rect($startX+110,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+110,$startY,4,4);
    }
    $this::setXY($startX + 115, $startY);
    $this::Cell(30,4,"2nd Sem",0,0,'L');

    if ($this->reg->Semester == 9){
      $this::Rect($startX+130,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+130,$startY,4,4);
    }
    $this::setXY($startX + 135, $startY);
    $this::Cell(30,4,"Summer",0,0,'L');

    $startX = 30;
    $startY += 5;
    if ($this->reg->StudentStatus == 'Shiftee'){
      $this::Rect($startX+5,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+5,$startY,4,4);
    }
    $this::setXY($startX + 10, $startY);
    $this::Cell(30,4,"Shiftee indicate previous course: ____________________",0,0,'L');

    $startY += 5;
    if ($this->reg->StudentStatus == 'Returnee'){
      $this::Rect($startX+5,$startY,4,4,'DF');
    }else{
      $this::Rect($startX+5,$startY,4,4);
    }
    $this::setXY($startX + 10, $startY);
    $this::Cell(30,4,"Returnee  :  AY/Sem. Last Attended __________________",0,0,'L');
    $startY += 5;
    $this::setXY($startX+5, $startY);
    $this::Cell(30,4,"____ graduating ____ non-graduating",0,0,'L');

    $startY += 8;
    $startX = 0;
    $this::SetFont('cambriab','',12);
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,mb_strtoupper($this->one->LastName),0,0,'C');

    $startX += 45;
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,mb_strtoupper($this->one->FirstName),0,0,'C');

    $startX += 45;
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,mb_strtoupper($this->one->MiddleName),0,0,'C');

    $startX += 45;
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,$this->reg->course->accro.(empty($this->reg->major->course_major)?'':'-'.substr($this->reg->major->course_major,0,20)),0,0,'C');

    $startY += 5;
    $startX = 0;
    $this::SetFont('cambria','',10);
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,'Last Name',0,0,'C');

    $startX += 45;
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,'First Name',0,0,'C');

    $startX += 45;
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,'Middle Name',0,0,'C');

    $startX += 45;
    $this::setXY($startX + 10, $startY);
    $this::Cell(45,4,'Course/Year Level/Major',0,0,'C');

    $header = ['Course Code','Course Number','Units'];
    $sizes = [60,80,40];

    $startY += 7;
    $startX = 13;

    $this::setXY($startX,$startY);
    for($i=0;$i<count($header);$i++)
    {
      $this::Cell($sizes[$i],6,$header[$i],1,0,'C');
    }

    $tblCC = "courseoffering".$this->reg->SchoolYear.$this->reg->Semester;
    $tblGrades = "grades".$this->reg->SchoolYear.$this->reg->Semester;
    $subjects = DB::connection(strtolower(session('campus')))->table($tblGrades." as g")
        ->select("t.courseno","t.units","t.exempt",
                "cc.coursecode")
        ->leftjoin($tblCC." as cc", "g.courseofferingid", "=", "cc.id")
        ->leftjoin("transcript as t", "g.sched", "=", "t.id")
        ->where("g.gradesid", $this->reg->RegistrationID)
        ->orderby("t.sort_order", "ASC")
        ->get();
    $totalUnits = 0;
    foreach($subjects as $row)
    {
      if ($row->exempt != 1){
        $totalUnits += $row->units;
      }
      $startY += 6;
      $startX = 13;
      $this::setXY($startX,$startY);
      $this::Cell($sizes[0],6,$row->coursecode,1,0,'C');
      $startX = 13+$sizes[0];
      $this::setXY($startX,$startY);
      $this::Cell($sizes[1],6,$row->courseno,1,0,'C');
      $startX = $sizes[1]+$sizes[0];
      $this::Cell($sizes[2],6,$row->units,1,0,'C');
    }

    if (count($subjects)<15){
      for($s = count($subjects) + 1; $s <= 15; $s++){
        $startY += 6;
        $startX = 13;
        $this::setXY($startX,$startY);
        $this::Cell($sizes[0],6,"",1,0,'C');
        $startX = 13+$sizes[0];
        $this::setXY($startX,$startY);
        $this::Cell($sizes[1],6,"",1,0,'C');
        $startX = $sizes[1]+$sizes[0];
        $this::Cell($sizes[2],6,"",1,0,'C');
      }
    }

    $startY += 6;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell($sizes[0],6,"",1,0,'C');
    $startX = 13+$sizes[0];
    $this::setXY($startX,$startY);
    $this::Cell($sizes[1],6,"Total Units ",1,0,'R');
    $startX = $sizes[1]+$sizes[0];
    $this::SetFont('cambriab','',12);
    $this::Cell($sizes[2],6,$totalUnits,1,0,'C');

    $this::SetFont('cambria','',10);
    $startY += 10;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(50,6,"Evaluated:",0,0,'L');

    $startX = 90;
    $this::setXY($startX,$startY);
    $this::Cell(50,6,"Approved:",0,0,'L');

    $startY += 15;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,$this->reg->EnrolName,0,0,'C');

    $startX = 100;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,$this->reg->ValidName,0,0,'C');

    $startY += 5;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,'Department Head/In-charge','T',1,'C');

    $startX = 100;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,'Registrar','T',1,'C');

    $startY += 5;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,(empty($this->reg->DateEnrolled)?"":date('F j, Y',strtotime($this->reg->DateEnrolled))),0,0,'C');

    $startX = 100;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,(empty($this->reg->DateValidated)?"":date('F j, Y',strtotime($this->reg->DateValidated))),0,0,'C');

    $startY += 5;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,'Date','T',1,'C');

    $startX = 100;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,'Date','T',1,'C');

    $startY += 5;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,'___ documentary requirements complete',0,0,'L');

    $startY += 5;
    $startX = 13;
    $this::setXY($startX,$startY);
    $this::Cell(80,6,'___ qualified for ranking     ___ disqualified for ranking',0,0,'L');



    $this->setLastY($startY);

  }

  public function Footer(){
    // $startY = $this->getLastY();
    $startY = 265;
    $startX = 15;
    $this::SetFont($this->cambria,'',7.5);
    $this::setY($startY);
    $this::setX($startX);
    $this::MultiCell(0, 3, "Note: This enrollment form is system validated, hence signatures of Student, Department Head/In-charge, and Registrar are not evident.",0,"L");
    $this->letter->ReportFooter(['QC' => config('QC.RE09')]);
  }

  public function List()
  {

    $startY = $this->getLastY();
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
