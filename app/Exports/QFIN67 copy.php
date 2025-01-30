<?php

namespace App\Exports;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Rmunate\Utilities\SpellNumber;
use Crypt;
use App\Models\Enrolled;
use GENERAL;
use DateTime;
use Preference;

class QFIN67 extends Controller
{

  protected $data;

  public function generate()
  {


      try{

        $id = Crypt::decryptstring($this->getData()['id']);
        $sy = Crypt::decryptstring($this->getData()['sy']);
        $sem = Crypt::decryptstring($this->getData()['sem']);

      }catch(DecryptException $e){
        return GENERAL::Error("Invalid Hash");
      }


      session([
        'schoolyear' => $sy,
        'semester' => $sem
      ]);

      $cc = "courseoffering".$sy.$sem;
      $enrolled = new Enrolled();

      $clsprefs = new Preference();
      $prefs = $clsprefs->GetDefaults(['RequestAmountPT','RequestAmountRG']);

      $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
        'c.course_title','r.StudentYear','r.Section','r.SchoolLevel','s.StudentNo',
        's.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
        'm.course_major','s.civil_status','s.BirthDate','s.p_street', 's.p_municipality', 's.p_province',
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
        ->where("r.SchoolYear", $sy)
        ->where("r.Semester", $sem)
        ->where("r.finalize", 1)
        ->orderBy("s.LastName")
        ->orderBy("s.FirstName")
        ->get();

      $divisor = count($lists);
      $honorariumpt = $clsprefs->GetDefaultValue($prefs, "RequestAmountPT");
      $honorariumrg = $clsprefs->GetDefaultValue($prefs, "RequestAmountRG");

      $job = "pt";
      if (!empty($lists[0]->EmploymentStatus)){
          if (strtolower($lists[0]->EmploymentStatus) == "permanent - faculty"){
            $job = "reg";
          }

          if (strtolower($lists[0]->EmploymentStatus) == "temporary - faculty"){
            $job = "reg";
          }
      }


      if ($job == 'pt'){
        if (empty($honorariumpt)){
          $feeperstudent = 0;
        }else{
          $lab = (empty($lists[0]->lab)?0:$lists[0]->lab);
          $lec = (empty($lists[0]->lec)?0:$lists[0]->lec);
          $hours = ($lab * 3) + $lec ;
          $feeperstudent = (($honorariumpt * $hours) * 18) / $divisor;
        }
      }else{
        if (empty($honorariumrg)){
          $feeperstudent = 0;
        }else{
          $lab = (empty($lists[0]->lab)?0:$lists[0]->lab);
          $lec = (empty($lists[0]->lec)?0:$lists[0]->lec);
          $hours = ($lab * 3) + $lec;
          $feeperstudent = (($honorariumrg * $hours) * 18) / $divisor;
        }
      }

      // dd($lists);
      // Load the existing Word document
      $templatePath = storage_path('app/private/word/QFIN67.docx');
      foreach($lists as $list){
        $templateProcessor = new TemplateProcessor($templatePath);

        if (empty($list['BirthDate'])){
            $age = 0;
        }else{
            $tmpage = explode(" ", $list['BirthDate']);
            $bdate2 = $tmpage[2]."-".$tmpage[0]."-".$tmpage[1];
            $from = new DateTime($bdate2);
            $to   = new DateTime('today');
            $age = $from->diff($to)->y;
        }

        // Add values to the document
        $templateProcessor->setValue('SY2', GENERAL::setSchoolYearLabel($sy,$sem)." ");
        $templateProcessor->setValue('SY', "For ".GENERAL::Semesters()[$sem]['Long']. ' Academic Year '.GENERAL::setSchoolYearLabel($sy,$sem));
        $templateProcessor->setValue('SEM', GENERAL::Semesters()[$sem]['Long']);
        $templateProcessor->setValue('Name', strtoupper(utf8_decode($list->FirstName. (empty($list->MiddleName)? " " : " ".$list->MiddleName[0].". "). $list->LastName)));
        $templateProcessor->setValue('CivilStatus', $list->civil_status);
        $templateProcessor->setValue('Age', $age);
        $templateProcessor->setValue('Address', strtoupper(utf8_decode($list->p_street. ', '.$list->p_municipality.', '.$list->p_province)));
        $templateProcessor->setValue('Course', $list->course_title);
        $templateProcessor->setValue('Campus', GENERAL::Campuses()[session('campus')]['Campus']);
        $templateProcessor->setValue("Reason", " honorarium of the faculty handling petitioned subject (".$list->courseno.")");

        $spell = SpellNumber::value(number_format($feeperstudent,2))->toLetters();
        $templateProcessor->setValue('Words', strtoupper($spell));
        $templateProcessor->setValue('Figure', number_format($feeperstudent,2));

        $public = "public";
        $directoryPath = 'out-out-qfin67/'.session('campus');
        if (!Storage::exists($public."/".$directoryPath)) {
          Storage::makeDirectory($public."/".$directoryPath);
        }

        // Save the modified document
        $outputPath = storage_path('app/public/'.$directoryPath.'/'.\Str::slug($list->FirstName.'-'.$list->LastName).'.docx');
        $templateProcessor->saveAs($outputPath);
      }

      // return response()->download($outputPath);
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
}

?>
