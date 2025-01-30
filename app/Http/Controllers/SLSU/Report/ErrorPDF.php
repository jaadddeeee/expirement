<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use GENERAL;

class ErrorPDF extends TCPDF
{
    protected $letter;
    protected $name;
    protected $error;
    protected $prefs;
    protected $pref;

    public function __construct(){
        $this->letter = new LetterHead();
        $this->prefs = new Preference();
        $this->pref = $this->prefs->GetDefaults();
    }

    public function Header(){

      // dd($this->data[0]->subject);
      $this->letter->ReportHeader();

      $startY = 45;

      $registrar = strtoupper("Error");

      $this::setXY(17, $startY);

			$this::SetFont('cambriab','',12);
			$this::Cell(180,5,$registrar,0,0,'C');

			$startY += 10;
    }

    public function Footer(){
      $this->letter->ReportFooter(['QC' => config('QC.GoodMoral')]);
    }

    function Content()
		{
      $this->setName("Error");
      $startY = 65;
      // $this::setXY(17, $startY);
			// $this::SetFont('cambria','',12);
			// $this::Cell(160,5,date('F d, Y'),0,0,'R');

      // $startY += 20;
      $this::setXY(17, $startY);
			$this::SetFont('cambriab','',16);
			$this::Cell(180,5,"E R R O R",0,0,'C');

      $startY += 20;

      $startY += 10;
      $this::setXY(25, $startY);
			$this::SetFont('cambria','',12);
      $this::setCellPadding(0);
      $this::Ln();
      $html = '<div style="text-align:justify;line-height: 20px;text-indent: 30px;">'.$this->getError().'</div>';
      $this::writeHTML($html, true, 0, true, true);

      $this::Ln(15);
      $this::lastPage();

		}



    /**
     * Get the value of error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */
    public function setError($error)
    {
        $this->error = $error;

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
