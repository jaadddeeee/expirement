<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Preference;
use App\Models\VARSITY\Scuaa;
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
      $this::setXY(35, 17);
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

  public function ScuaaHeaderLandScape(){
    $year = date('Y'); // Get the current year

    // Fetch the ScuaaLogo where the Date column contains the current year
    $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $year . '%')
        ->select('*')
        ->first();

        $title = $scuaaList->Title;
        $dateLocation = $scuaaList->Date .', '. $scuaaList->University .', '. $scuaaList->Location;
        $logoPath = ('storage/' . $scuaaList->ScuaaLogo);

    $x = 20;
    $yl = 3;
    $y=8;
    $this::SetTextColor(0,0,0);
    $this::Image($logoPath, $x, $yl, 24.9, 25);
    $x+=28;
    $this::setXY($x, $y);
    $this::SetFont('arialb','',16);
    $this::Cell(30,5,$title,0,0,'L');
    $y-=1;
    $this::SetFont('lucidafaxdemib', '', 10);
    $html = '<div style="text-align: justify; word-wrap: break-word; line-height: 1.2;">
                <p>'.$dateLocation.'</p>
            </div>';
    $this::writeHTMLCell(156, 0, $x , $y, $html, 0, 1, false, true, 'L');

    // Add vertical line
    $this::SetLineWidth(0.4);
    $this::Line(205, 17, 205, 30);

    $this::Line(325, 17, 325, 30);

    //horizontal line short
    $this::Line(205, 17, 325, 17);

    // Add horizontal line
    $this::SetLineWidth(0.7);
    $this::Line(5, 30, 325, 30);

    // Add "OFFICIAL ENTRY FORM AND GALLERY OF" text with border
    $this::setXY(228, 14);
    $this::SetFont('lucidafaxdemib','',11);
    $this::Cell(70, 15, 'OFFICIAL ENTRY FORM AND GALLERY OF', 0, 0, 'C');
  }

  public function ScuaaHeader(){
    $year = date('Y'); // Get the current year

    // Fetch the ScuaaLogo where the Date column contains the current year
    $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $year . '%')
        ->select('*')
        ->first();

    preg_match('/\d{4}/', $scuaaList->Date, $matches);
    $year = ($matches ? $matches[0] : '');
    $dateLocation = $scuaaList->Date .', '. $scuaaList->University .', '. $scuaaList->Location;
    $logoPath = ('storage/' . $scuaaList->ScuaaLogo);

    $x = 14;
    $yl = 8;
    $y=6;

    $this::SetTextColor(0,0,0);
    $this::Image($logoPath, $x, $yl, 41, 41);

    $x+=76;
    $this::setXY($x, $y);
    $this::SetFont('bodonimtb','',18);
    $this::Cell(30,5,'REGIONAL SCUAA GAMES',0,0,'C');

    $y+=8;
    $this::SetFont('bodonimtb','',16);
    $this::setXY($x, $y);
    $this::Cell(30,5,$year,0,0,'C');
    
    $y+=8;
    $this::SetFont('lucidafaxdemib','',11);
    $this::setXY($x, $y);
    $this::Cell(30,5,'Host: '. $scuaaList->University,0,0,'C');

    $y+=5;
    $this::setXY($x, $y);
    $this::SetFont('lucidafaxdemib','',10);
    $this::Cell(30,5,$scuaaList->Date,0,0,'C');

    $y+=5;
    $this::setXY($x, $y);
    $this::SetFont('lucidafaxdemibi','',10.5);
    $this::Cell(30,5,'Theme: '. $scuaaList->Theme,0,0,'C');

    $y+=9;
    $this::setXY($x, $y);
    $this::SetFont('elephantdarkness','B',14);
    $this::Cell(30,10,'ELIGIBILITY FORM ',0,0,'C');
  }
}
