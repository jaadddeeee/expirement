<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use GENERAL;

use App\Models\Student;

class Endorsement extends TCPDF
{
    protected $letter;
    protected $name;
    protected $studentno;
    protected $prefs;
    protected $pref;
    protected $LatinHonor;
    public function __construct(){
        $this->letter = new LetterHead();
        $this->prefs = new Preference();
        $this->pref = $this->prefs->GetDefaults();
    }

    public function Header(){

      // dd($this->data[0]->subject);
      $this->letter->ReportHeader();

      $startY = 45;

      if (strtolower(session('campus')) == "sg"){
        $registrar = strtoupper("Office of the University Registrar");
      }else{
        $registrar =  strtoupper("Office of the Registrar");
      }

      $this::setXY(17, $startY);
			$this::SetFont('cambriab','',12);
			$this::Cell(180,5,$registrar,0,0,'C');

			$startY += 10;
    }

    public function Footer(){
      $this->letter->ReportFooter(['QC' => config('QC.RO20')]);
    }


    function Content()
		{

      $student = Student::where("StudentNo", $this->getStudentno())->first();
      $this->setName($student->FirstName.' '.$student->LastName);


      $startY = 65;
      // $this::setXY(17, $startY);
			// $this::SetFont('cambria','',12);
			// $this::Cell(160,5,date('F d, Y'),0,0,'R');

      // $startY += 20;

      $this::setXY(140, $startY);
			$this::SetFont('cambria','',12);
			$this::Cell(50,5,date('F j, Y'),0,0,'C');
      $startY += 5;
      $this::setXY(140, $startY);
			$this::SetFont('cambria','',12);
			$this::Cell(50,5,"Date",0,0,'C');

      $startY += 20;
      $this::setXY(17, $startY);
			$this::SetFont('cambriab','',16);
			$this::Cell(180,5,"E N D O R S E M E N T",0,0,'C');




      $startY += 10;
      $this::setXY(25, $startY);
			$this::SetFont('cambria','',12);
      $this::setCellPadding(0);
      $this::Ln();

      $html = '<div style="text-align:justify;line-height: 25px;text-indent: 30px;">Respectfully forwarded to the Office of the University President the herein attached
school credentials of <strong>'.($student->Sex=="M"?"MR.":"MS.").' '.strtoupper(utf8_decode($student->FirstName.(empty($student->MiddleName)?" ":" ".$student->MiddleName[0].". ").$student->LastName)).'</strong> a graduate with the degree of <strong>'.strtoupper($student->course->course_title)." (".strtoupper($student->course->accro).') </strong>'.(empty($student->Major->course_major)?"":(strtolower($student->Major->course_major) == "none" ? "": " major in <strong>".strtoupper($student->Major->course_major)."</strong>")).(empty($this->getLatinHonor())?"":" as <strong>".$this->getLatinHonor()."</strong>").' in this
University, Academic Year <strong>'.date('Y', strtotime($student->grad)) - 1 . "-".date('Y', strtotime($student->grad)).'</strong> for the issuance of Certification, Authentication and
Verification (CAV).</div>';
      $this::writeHTML($html, true, 0, true, true);

      $this::Ln(15);

      $this::SetFont('cambriab','',12);
      $this::setX(100);
			$this::Cell(100,5,$this->prefs->GetDefaultValue($this->pref, "RegistrarName"),0,0,'C');
      $this::Ln();
      $this::SetFont('cambria','',12);
      $this::setX(100);
			$this::Cell(100,5,$this->prefs->GetDefaultValue($this->pref, "RegistrarLevel"),0,0,'C');
      $this::Ln(25);

      $x = 25;
      // $this::setXY($x, $startY);
      $this::Cell(100,5,"Enclosure:",0,0,'L');
      $this::Ln(10);
      $this::setX($x + 5);
      $this::Cell(100,5,"Transcript of Records",0,0,'L');
      $this::Ln();
      $this::setX($x + 5);
      $this::Cell(100,5,"Certification",0,0,'L');
      $this::Ln();
      $this::setX($x + 5);
      $this::Cell(100,5,"Diploma",0,0,'L');
      $this::lastPage();

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
     * Get the value of studentno
     */
    public function getStudentno()
    {
        return $this->studentno;
    }

    /**
     * Set the value of studentno
     *
     * @return  self
     */
    public function setStudentno($studentno)
    {
        $this->studentno = $studentno;

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

    /**
     * Get the value of LatinHonor
     */
    public function getLatinHonor()
    {
        return $this->LatinHonor;
    }

    /**
     * Set the value of LatinHonor
     *
     * @return  self
     */
    public function setLatinHonor($LatinHonor)
    {
        $this->LatinHonor = $LatinHonor;

        return $this;
    }
}
