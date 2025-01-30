<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Preference;
use GENERAL;

class LetterHead extends TCPDF
{
  protected $prefs;
  protected $pref;

  public function __construct(){
    $this->prefs = new Preference();
    $this->pref = $this->prefs->GetDefaults();
    session([
      'Registrar' => $this->prefs->GetDefaultValue($this->pref, "RegistrarName"),
      'RegistrarRank' => $this->prefs->GetDefaultValue($this->pref, "RegistrarLevel"),
      'President' => $this->prefs->GetDefaultValue($this->pref, "PresidentName"),
    ]);
  }

  public function ReportHeaderOld(){

      $this::SetTextColor(0,58,117);
      $this::SetFont('trajanpro','',19);
      $this::Image(GENERAL::Logo(),15,7,20);
      $this::setXY(35, 11);
      $this::Cell(30,5,"Southern Leyte",0,0,'L');
      $this::setXY(35, 18);
      $this::Cell(30,5,"State University",0,0,'L');

      $x = 115;

      $this::SetFont("cambria",'',10);
      $this::setXY($x, 8);
      $this::Cell(30,5,$this->prefs->GetDefaultValue($this->pref, "CampusString"),0,0,'L');

      $this::setXY($x, 12);
      $this::Cell(30,5,$this->prefs->GetDefaultValue($this->pref, "SchoolAddress"),0,0,'L');

      //WITH CONTACT NUMBER
      $this::setXY($x, 16);
      $this::Cell(30,5,"Contact No: ". $this->prefs->GetDefaultValue($this->pref, "SchoolContactNo"),0,0,'L');

      $this::setXY($x, 20);
      $this::Cell(30,5,"Email: ". $this->prefs->GetDefaultValue($this->pref, "SchoolEmail"),0,0,'L');
      $this::setXY($x, 24);
      $this::Cell(30,5,"Website: ". $this->prefs->GetDefaultValue($this->pref, "SchoolWebsite"),0,0,'L');

      $this::SetTextColor(0,0,0);
      $this::setXY(15, 30);
      $this::SetFont('cambria','',8);
      $this::Cell(180,5,"Excellence | Service | Leadership and Good Governance | Innovation | Social Responsibility | Integrity | Professionalism | Spirituality",0,0,'C');
      $this::Line(15, 35, 200, 35);
  }

  public function ReportHeaderLandScape(){

    $x = 100;
    $y=7;
    $this::SetTextColor(0,58,117);
    $this::SetFont('trajanpro','',17);
    $this::Image(GENERAL::Logo(),$x,$y,20);
    $this::Image(GENERAL::Pilipinas(),$x+110,$y,20);
    $x+=22;
    $this::setXY($x, $y);
    $this::Cell(30,5,"Southern Leyte",0,0,'L');
    $y+=5;
    $this::setXY($x, $y);
    $this::Cell(30,5,"State University",0,0,'L');
    $this::SetFont("cambria",'',8);
    $y+=6;
    $this::setXY($x, $y);
    $this::Cell(30,3,$this->prefs->GetDefaultValue($this->pref, "CampusString").", ".$this->prefs->GetDefaultValue($this->pref, "SchoolAddress"),0,0,'L');

    //WITH CONTACT NUMBER
    // $this::setXY($x, 16);
    // $this::Cell(30,5,"Contact No: ". $this->prefs->GetDefaultValue($this->pref, "SchoolContactNo"),0,0,'L');

    $y+=2;
    $this::setXY($x, $y);
    $this::Cell(30,5,"Email: ". $this->prefs->GetDefaultValue($this->pref, "SchoolEmail"),0,0,'L');
    $y+=3;
    $this::setXY($x, $y);
    $this::Cell(30,5,"Website: ". $this->prefs->GetDefaultValue($this->pref, "SchoolWebsite"),0,0,'L');

    $this::SetTextColor(0,0,0);
    $this::setXY(65, 30);
    $this::SetFont('cambria','',8);
    $this::Cell(200,5,"Excellence | Service | Leadership and Good Governance | Innovation | Social Responsibility | Integrity | Professionalism | Spirituality",0,0,'C');
    $this::Line(15, 35, 320, 35);
  }

  public function ReportHeader(){

    $x = 40;
    $y=7;
    $this::SetTextColor(0,58,117);
    $this::SetFont('trajanpro','',17);
    $this::Image(GENERAL::Logo(),$x,$y,20);
    $this::Image(GENERAL::Pilipinas(),$x+110,$y,20);
    $x+=22;
    $this::setXY($x, $y);
    $this::Cell(30,5,"Southern Leyte",0,0,'L');
    $y+=5;
    $this::setXY($x, $y);
    $this::Cell(30,5,"State University",0,0,'L');

    $this::SetFont("cambria",'',8);
    $y+=6;
    $this::setXY($x, $y);
    $this::Cell(30,3,$this->prefs->GetDefaultValue($this->pref, "CampusString").", ".$this->prefs->GetDefaultValue($this->pref, "SchoolAddress"),0,0,'L');

    //WITH CONTACT NUMBER
    // $this::setXY($x, 16);
    // $this::Cell(30,5,"Contact No: ". $this->prefs->GetDefaultValue($this->pref, "SchoolContactNo"),0,0,'L');

    $y+=2;
    $this::setXY($x, $y);
    $this::Cell(30,5,"Email: ". $this->prefs->GetDefaultValue($this->pref, "SchoolEmail"),0,0,'L');
    $y+=3;
    $this::setXY($x, $y);
    $this::Cell(30,5,"Website: ". $this->prefs->GetDefaultValue($this->pref, "SchoolWebsite"),0,0,'L');

    $this::SetTextColor(0,0,0);
    $this::setXY(15, 30);
    $this::SetFont('cambria','',8);
    $this::Cell(180,5,"Excellence | Service | Leadership and Good Governance | Innovation | Social Responsibility | Integrity | Professionalism | Spirituality",0,0,'C');
    $this::Line(15, 35, 200, 35);
  }

  public function ReportFooter($data = []){
    $startY = 257;
    $startX = 15;

    $startY = 270;
    $this::Line(15, $startY, 200, $startY);
    $startY += 5;
    $this::setY($startY);
    $this::setX($startX);
    $this::SetFont('cambria','', 7);
    $this::MultiCell(50, 3, (isset($data['QC'])?$data['QC']:""),0,"L");

    $startX = 80;
    $this::setY($startY);
    $this::setX($startX);
    $this::Image(GENERAL::QStarLogo(),$startX,$startY-3,40);

    $startX = 160;
    $this::Image(GENERAL::ISOLogo(),$startX,$startY-3,30);
  }

  public function ReportFooterLandScape($data = []){
    $startX = 15;

    $startY = 192;
    $this::Line(15, $startY, 320, $startY);
    $startY += 5;
    $this::setY($startY);
    $this::setX($startX);
    $this::SetFont('cambria','', 7);
    $this::MultiCell(50, 3, (isset($data['QC'])?$data['QC']:""),0,"L");

    $startX = 240;
    $this::setY($startY);
    $this::setX($startX);
    $this::Image(GENERAL::QStarLogo(),$startX,$startY-4,40);

    $startX = 280;
    $this::Image(GENERAL::ISOLogo(),$startX,$startY-3,25);
  }

}
