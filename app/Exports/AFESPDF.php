<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Support\Facades\DB;

use GENERAL;
use App\Models\FacultyEvaluationSchedule;
class AFESPDF extends TCPDF
{

  protected $id;
  protected $one;
  protected $lastY;

  protected $name;
  public function __construct(){
    $this->letter = new LetterHead();
    $this->cambria = 'cambria';
    $this->cambriabold = 'cambriab';
    $this->deffontsize = 9;
    $this->header = ['#',' Faculty Name','Department','Commitment','Knowledge of','Teaching for','Management of'];
    $this->header2 = ['','','','','Subject','Independent Learning','Learning'];
    $this->sizes = [10,46,20,27,27,27,27];
  }

  private function generate(){

    $this->sched = FacultyEvaluationSchedule::find($this->getId());

    $tblsummary = "facultyevaluationsummary".$this->sched->SchoolYear.$this->sched->Semester;
    $this->one = DB::connection('evaluation')
      ->table($tblsummary)
      ->orderby('LastName')
      ->orderby('FirstName')
      ->get();
    $this->setName(\Str::slug(GENERAL::setSchoolYearLabel($this->sched->SchoolYear, $this->sched->Semester).'-'.GENERAL::Semesters()[$this->sched->Semester]['Long']));
  }

  public function Header(){
    $this->generate();
    $this->letter->ReportHeader();
    $startY = 36;
    $startY += 8;
    $startX = 45;
    $this::setXY($startX, $startY);

    $this::SetFont('cambriab','',12);
    $this::MultiCell(110,4,"AUTOMATED FACULTY EVALUATION SYSTEM (AFES) OFFICIAL RESULT",0,'C');

    $startY += 15;
    $this::setXY($startX, $startY);
    $this::SetFont('cambria','',10);
    $this::MultiCell(110,4,"Period: AY ".GENERAL::setSchoolYearLabel($this->sched->SchoolYear, $this->sched->Semester).' - '.GENERAL::Semesters()[$this->sched->Semester]['Long'],0,'C');

    $startY += 10;

    $startX = 15;
    $this::SetFont($this->cambria,'',7);
    $this::setXY($startX,$startY);
    for($i=0;$i<count($this->header);$i++)
    {
      if ($i == 1){
        $this::Cell($this->sizes[$i],6,$this->header[$i],'TLR',0,'L');
      }else{
        $this::Cell($this->sizes[$i],6,$this->header[$i],'TLR',0,'C');
      }

    }
    $startY += 3;
    $this::setXY($startX,$startY);
    for($i=0;$i<count($this->header2);$i++)
    {
      $this::Cell($this->sizes[$i],6,$this->header2[$i],'BLR',0,'C');
    }

    $this->setLastY($startY);

  }

  public function Footer(){
    // $startY = $this->getLastY();
    $startY = 243;
    $startX = 17;
    $this::setY($startY);
    $this::setX($startX);
    $this::SetFont($this->cambria,'',8);
    $this::Cell(184,5,"Prepared by:",0,0,'L');
    $startY += 10;
    $this::SetFont($this->cambriabold,'',8);
    $this::setY($startY);
    $this::setX($startX);
    $this::Cell(184,5,"FRANCIS REY F. PADAO, MIM",0,0,'L');
    $startY += 5;
    $this::setY($startY);
    $this::setX($startX);
    $this::SetFont($this->cambria,'',8);
    $this::Cell(184,5,"Director, UISA",0,0,'L');
    $startY = 272;
    $this::SetFont($this->cambria,'',7.5);
    $this::setY($startY);
    $this::setX($startX);
    $this::Cell(184,5,"Page ".$this::PageNo()." / ".$this::getAliasNbPages(),0,0,'L');
    $this->letter->ReportFooter();
  }

  public function List()
  {
    $startY = $this->getLastY();
    $startY += 6;
    $startX = 15;
    $this::setY($startY);
    $this::setX($startX);

    $this->setLastY($startY);
    foreach(GENERAL::Campuses() as $index => $campus){
      unset($ctr);
      $this::SetFont($this->cambriabold,'',8);
      $this::SetFillColor(4, 46, 98);
      $this::SetTextColor(255, 255, 255);
      $this::Cell(184,5," ".$campus['Campus'],1,0,'L','1');
      $this::Ln();

      $this::SetFont($this->cambria,'',8);
      $this::SetFillColor(255, 255, 255);
      $this::SetTextColor(0, 0, 0);
      foreach($this->one as $one){
        if ($one->Campus == $index)
        {
          if ($one->P1 > 0){
            $this::SetFont($this->cambria,'',8);
            $this::Cell($this->sizes[0],5,isset($ctr)?++$ctr:$ctr=1,'TLR',0,'C');
            $this::Cell($this->sizes[1],5," ".strtoupper($one->LastName.', '.$one->FirstName),'TLR',0,'L');
            $this::Cell($this->sizes[2],5," ".$one->DepartmentName,'TLR',0,'L');
            $this::Cell($this->sizes[3],5," ".$one->C1,'TLR',0,'C');
            $this::Cell($this->sizes[4],5," ".$one->C2,'TLR',0,'C');
            $this::Cell($this->sizes[5],5," ".$one->C3,'TLR',0,'C');
            $this::Cell($this->sizes[6],5," ".$one->C4,'TLR',0,'C');
            $this::Ln();
            $this::SetFont($this->cambria,'',7);
            $this::Cell($this->sizes[0],3,'','BLR',0,'C');
            $this::Cell($this->sizes[1],3," ",'BLR',0,'L');
            $this::Cell($this->sizes[2],3," ",'BLR',0,'L');
            $this::Cell($this->sizes[3],3,$one->P1 . ' ('.GENERAL::AdjectiveRating($one->P1, 'No').')','BLR',0,'C');
            $this::Cell($this->sizes[4],3,$one->P2 . ' ('.GENERAL::AdjectiveRating($one->P2, 'No').')','BLR',0,'C');
            $this::Cell($this->sizes[5],3,$one->P3 . ' ('.GENERAL::AdjectiveRating($one->P3, 'No').')','BLR',0,'C');
            $this::Cell($this->sizes[6],3,$one->P4 . ' ('.GENERAL::AdjectiveRating($one->P4, 'No').')','BLR',0,'C');
            $this::Ln();
          }
        }
      }
    }

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
