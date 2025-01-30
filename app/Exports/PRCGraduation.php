<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;

use GENERAL;
use App\Models\Student;
class PRCGraduation extends TCPDF
{

  protected $id;
  protected $sy;
  protected $sem;
  protected $lists;
  public function __construct(){
    $this->letter = new LetterHead();
    $this->width = [10,30,28,28,20,70,60,20,20,20];
    $this->caption = [
      'NO','LAST NAME','FIRST NAME','MIDDLE NAME','BIRTH DATE','COURSE','SCHOOL',
      'DATE GRAD','SO NO','SO DATE'
    ];
  }

  private function generate(){

    $lists = Student::select('students.*','c.course_title')
        ->leftjoin("course as c", "students.Course", "=", "c.id")
        ->where('grad', $this->getId())
        ->orderby("LastName")
        ->orderby("FirstName")
        ->get();
    return $lists;
  }

  public function Header(){

    $this->lists = $this->generate();
    $this->letter->ReportHeaderLandScape();
    $startY = 40;

    $this::setXY(15, $startY);

    $this::SetFont('cambriab','',12);
    $this::Cell(300,5,"LIST OF GRADUATE",0,0,'C');

    $startY += 5;
    $this::setXY(17, $startY);
    $this::SetFont('cambria','',10);
    $this::Cell(300,5,GENERAL::Semesters()[$this->getSem()]['Long']. ', AY '.GENERAL::setSchoolYearLabel($this->getSy(),$this->getSem()),0,0,'C');

    $startY += 7;
    $ctr = 0;
    $cWidth = 15;
    $this::SetFont('cambria','',9);
    foreach($this->width as $w){

      $this::setXY($cWidth, $startY);
      $this::Cell($w,5,$this->caption[$ctr],1,0,'C');
      $ctr++;
      $cWidth += $w;
    }

  }

  public function Footer(){
    $startY = 196;
    $this::SetFont('cambria','',8);
    $this::setXY(0, $startY);
    $this::Cell(70, 4, "Page ".$this::PageNo()." of ".$this::getAliasNbPages() ." pages" ,0,0,"R");
    $this->letter->ReportFooterLandScape();
  }

  public function Body()
  {
    $startY = 57;

    $cWidth = 15;
    $interval = 5;
    $this::setXY($cWidth, $startY);
    $this::SetFont('cambria','',8);
    foreach($this->lists as $list){
      set_time_limit(0);
      $datebirth = "";
      if (!empty($list->BirthDate)){
        $tmp = explode(" ",$list->BirthDate);
        $datebirth = $tmp[2]."-".str_pad($tmp[0],2,"0",STR_PAD_LEFT)."-".str_pad($tmp[1],2,"0",STR_PAD_LEFT);
      }

      $dategrad = $list->grad;
      // var_dump($dategrad);
      if (!empty($list->grad)){
        $dategrad = date('Y-m-d', strtotime($list->grad));
      }

      $this::Cell($this->width[0],$interval,(isset($ctr)?++$ctr:$ctr=1),1,0,'C');
      $this::Cell($this->width[1],$interval,strtoupper(utf8_decode($list->LastName)),1,0,'L');
      $this::Cell($this->width[2],$interval,strtoupper(utf8_decode($list->FirstName)),1,0,'L');
      $this::Cell($this->width[3],$interval,strtoupper(utf8_decode($list->MiddleName)),1,0,'L');
      $this::Cell($this->width[4],$interval,$datebirth,1,0,'C');
      $this::Cell($this->width[5],$interval,$list->course_title,1,0,'L');
      $this::Cell($this->width[6],$interval,'Southern Leyte State University-'.GENERAL::Campuses()[session('campus')]['Campus'],1,0,'L');
      $this::Cell($this->width[7],$interval,$dategrad,1,0,'C');
      $this::Cell($this->width[8],$interval,'BOR NO. '.$list->bor,1,0,'L');
      $this::Cell($this->width[9],$interval,$list->bordate,1,0,'C');


      // $startY += 5;
      $this::Ln();
      $this::setX($cWidth);
    }


    $this::Ln();
    $this::setX($this->width[0]+$this->width[1]+$this->width[2]-15);
    $this::Cell($this->width[4],$interval,"Prepared by:",0,0,'L');

    $this::setX($this->width[0]+$this->width[1]+$this->width[2]+$this->width[3]+$this->width[4]+$this->width[5]);
    $this::Cell($this->width[4],$interval,"Noted:",0,0,'L');

    $this::SetFont('cambriab','',9);
    $this::Ln();
    $this::Ln();
    $this::setX($this->width[0]+$this->width[1]+$this->width[2]-15);
    $this::Cell($this->width[5],$interval,strtoupper(session('Registrar')),0,0,'C');

    $this::setX($this->width[0]+$this->width[1]+$this->width[2]+$this->width[3]+$this->width[4]+$this->width[5]);
    $this::Cell($this->width[5],$interval,strtoupper(session('President')),0,0,'C');

    $this::SetFont('cambria','',8);
    $this::Ln();
    $this::setX($this->width[0]+$this->width[1]+$this->width[2]-15);
    $this::Cell($this->width[5],$interval,session('RegistrarRank'),0,0,'C');

    $this::setX($this->width[0]+$this->width[1]+$this->width[2]+$this->width[3]+$this->width[4]+$this->width[5]);
    $this::Cell($this->width[5],$interval,"University President",0,0,'C');
    $this::Ln();$this::Ln();$this::Ln();
    // $this::Cell($this->width[5],$interval,"University President",0,0,'C');
    $this::SetFont('cambria','',9);
    $html = "SUBSCRIBED AND SWORN to before me this ______________________ by the above-named officials who are personally known to me.";
    $this::writeHTMLCell(250, 10, 17, $this::getY(), $html, 0, 0, 0, true, 'J');
  }

  /**
   * Get the value of data
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Set the value of data
   *
   * @return  self
   */
  public function setData($data)
  {
    $this->data = $data;

    return $this;
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

?>
