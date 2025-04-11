<?php

namespace App\Http\Controllers\SLSU\Report\Varsity;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;
use App\Models\VARSITY\Scuaa;
use App\Models\VARSITY\CoachVarsity;
use App\Models\VARSITY\ListVarsity;
use GENERAL;

class ScuaaReport extends TCPDF
{
  protected $id;
  protected $sy;
  protected $event;
  protected $lists;
  public function __construct(){
    $this->letter = new LetterHead();
  }

  private function listVarsity()
  {
    // dd($this->getEvent()); 
    $query = ListVarsity::with('event')
    ->whereNull('deleted_at')
    ->where('Event', $this->getEvent())
    ->where('SchoolYear', $this->getSy())
    ->orderBy('SchoolYear', 'desc')
    ->get(); // Fetch all records
  
    // Fetch student data from multiple databases (batch query)
    $connections = ['sg', 'mcc', 'to', 'bn', 'sj', 'hn'];
    $studentNos = $query->pluck('StudentNo')->unique(); // Get unique Student Numbers once
    $allStudents = collect();
    
    foreach ($connections as $connection) {
        $students = Student::on($connection)
            ->whereIn('StudentNo', $studentNos)
            ->get()
            ->map(function ($student) use ($connection) {
              // Add the connection name to the student data for picture path
              $student->connection = $connection;
              return $student;
          });
        $allStudents = $allStudents->merge($students);
    }
    
    // Convert student collection into a key-value pair for **faster lookup**
    $studentLookup = $allStudents->keyBy('StudentNo');
    
    // Merge student details into varsity list and return as an array
    $varsityList = $query->map(function ($item) use ($studentLookup) {
        $student = $studentLookup[$item->StudentNo] ?? null;

        $fullName = ($student->LastName ?? 'N/A') . ', ' . ($student->FirstName ?? 'N/A') .
        (!empty($student->MiddleName) ? ' ' . $student->MiddleName[0] . '.' : '');

        $dob = !empty($student->BirthDate) 
          ? \DateTime::createFromFormat('d/m/Y', $student->BirthDate)?->format('F j, Y') ?? 'N/A'
          : 'N/A';

        $picture = ('storage/photo/'. strtoupper($student->connection).'/' . $student->Picture);

        // Separate the gender from the event name
        $eventName = optional($item->event)->event ?? 'N/A';
        $gender = null;

        if (str_contains(strtolower($eventName), 'women')) {
            $eventName = str_ireplace('women', '', $eventName);
            $gender = 'Women';
        } elseif (str_contains(strtolower($eventName), 'men')) {
            $eventName = str_ireplace('men', '', $eventName);
            $gender = 'Men';
        }

        $this->setGender(\Str::slug($gender));
        $this->setSport(\Str::slug($eventName));
    
        return [
            'SchoolYear' => $item->SchoolYear,
            'StudentNo'  => $item->StudentNo,
            'FullName'  => $fullName,
            'DOB'        => $dob,
            'CourseYear' => isset($student->Course, $student->StudentYear) 
                              ? "{$student->Course} - {$student->StudentYear}" 
                              : 'N/A',
            'Picture'    => $picture ?? null,
            'event_name' => strtoupper(trim($eventName)),
            'gender' => strtoupper($gender),
        ];
    });

    $listAthletes = $varsityList->sortBy('FullName')->values()->toArray();
  
    return $listAthletes; // Return as a list (array)

  }
  
  private function listCoaches()
  {
      $query = CoachVarsity::with('event','coach')
          ->whereNull('deleted_at')
          ->where('Event', $this->getEvent())
          ->where('SchoolYear', $this->getSy())
          ->orderBy('SchoolYear', 'desc')
          ->get(); // Fetch all records
  
      // Fetch employee data from hrmis.employee
      $employeeData = DB::connection('hrmis')
          ->table('employee')
          ->whereIn('id', $query->pluck('CoachID'))
          ->whereIn('campus', [1, 2, 3, 4, 5, 6])
          ->get()
          ->keyBy('id'); // Convert collection to key-value pair
    
          $campusToFolder = [
            1 => 'SG',
            2 => 'MCC',
            3 => 'TO',
            4 => 'BN',
            5 => 'SJ',
            6 => 'HN',
        ];
  
      // Merge employee details into coach list
      $coachList = $query->map(function ($item) use ($employeeData, $campusToFolder) {
          $emp = $employeeData->get($item->CoachID); // Retrieve employee by CoachID
          $coach = $item->coach; // Get the coach relationship

          $fullName = ($emp->LastName ?? 'N/A') . ', ' . ($emp->FirstName ?? 'N/A') .
          (!empty($emp->MiddleName) ? ' ' . $emp->MiddleName[0] . '.' : '');

          $campus = $emp->Campus ?? null;
          $folder = $campusToFolder[$campus] ?? 'UNKNOWN';

          $picture = ('storage/' . $coach->Picture);
  
          return [
              'SchoolYear' => $item->SchoolYear,
              'EmpID'      => $item->CoachID,
              'Email'     => $emp->EmailAddress ?? null,
              'ContactNo'  => $emp->Cellphone ?? null,
              'FullName'  => $fullName ?? null,
              'Picture'    => $picture ?? null,
              'event_name' => optional($item->event)->event ?? null,
          ];
      });

      $listCoaches = $coachList->sortBy('FullName')->values()->toArray();

      // dd($listCoaches);
  
      return $listCoaches; // Return the transformed list
  }
  
  public function Header(){
    $startYear = date('Y'); // Get the current year

    // Fetch the ScuaaLogo where the Date column contains the current year
    $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $startYear . '%')
        ->select('*')
        ->first();

    $this->setDate(\Str::slug($scuaaList->Date));

      $this->listAthletes = $this->listVarsity();
      $this->listCoaches = $this->listCoaches();
      $this->letter->ScuaaHeaderLandScape();
      $event = $this->listAthletes[0] ?? null;
      $startY = 40;
      // dd($this->varsityList);
      
      $this::setXY(15, $startY);
      $this::SetFont('calibrib','',11);
      $this::Cell(1,5,'SCHOOL:',0,0,'C');

      $this::setXY(255.3, 40);
      $this::Cell(1,5,'CATEGORY:',0,0,'C');
      
      $this::SetFont('calibri','',11);
      $this::setXY(250.3, 32);
      $this::Cell(1,5,'Date of Screening:',0,0,'C');

      $startY -= 1.9;
      $this::setXY(28, $startY);
      $this::SetFont('lucidafaxdemib','U',14);
      $this::Cell(2,5,strtoupper($scuaaList->University),0,1,'L');

      $this::setXY(304, 12);
      $this::SetFont('calibri','',10);
      $this::Cell(0,5,'SCUAA Form 2',0,1,'L');

      $this::SetFont('calibri','',12);
      $this::setXY(295, 32);
      $this::Cell(1,5,$this->getScreen(),0,0,'C');
      $this::Line(266, 37, 325, 37);
  
      $this::setXY(295, 39);
      $this::SetFont('calibrib','',14);
      $this::Cell(1,5,$event['gender'],0,0,'C');
      $this::Line(266, 44, 325, 44);
  
      $this::SetFont('lucidafaxdemib','',12);
      $this::setXY(265, 25);
      $this::Cell(1,5,$event['event_name'],0,0,'C');

      $this::Image(GENERAL::Logo(),7.9,65,30);
      // $this::Image(GENERAL::PASUCLogo(),7.5,135,30);
  }

  private function drawHeaders($startY, $cellHeight, $numColumns, $includeCoach = false, $numberCoach) {
      for ($col = 0; $col < $numColumns; $col++) {
          $this::SetXY(50 + ($col * 57), $startY - ($cellHeight - 1));
          $this::SetFont('calibrib', '', 12);
          $this::Cell(40, 51, 'ATHLETES', 0, 0, 'C');
          
      }

      // $startY += 51;

      if ($includeCoach) {
        for ($col = 0; $col < $numberCoach; $col++) {
          $this::SetXY(50 + ($col * 57), $startY - ($cellHeight - 1));
          $this::SetFont('calibrib', '', 12);
          $this::Cell(40, 51, 'COACH', 0, 0, 'C');
        }
      }
  }

  private function drawVertical($startY, $numColumns, $verticalHeight) {
        $this::Line(5 , $startY, 5, $startY + $verticalHeight);
  }
  
  private function drawLines($startY, $numColumns, $verticalHeight) {
      for ($col = 0; $col <= $numColumns; $col++) {
          $this::Line(40 + ($col * 57), $startY, 40 + ($col * 57), $startY + $verticalHeight);
      }
      
      foreach ([0, 54, 62, 67, 71.6] as $offset) {
          $this::Line(5, $startY + $offset, 325, $startY + $offset);
      }
  }

  private function drawLabels($startY, $type = 'default') {
    if($type == 'default'){
      $labels = ['Name', 'Date of Birth', 'Course & Year'];
      $offsets = [26.4, 33,37.5]; // Adjust vertical spacing dynamically
    }else {
      $labels = ['Name', 'Email', 'Contact No.'];
      $offsets = [26.4, 33,37.5]; // Adjust vertical spacing dynamically
    }
    
    $this::SetFont('calibri', '', 11);
    
    foreach ($offsets as $index => $offset) {
        $this::SetXY(6, $startY + $offset);
        $this::Cell(40, 10, $labels[$index], 0, 0, 'L');
    }
  }

  private function drawAthletes($startY, $cellHeight, $numColumns, $athletes)
  {
      $cols = 0;
      $row = 0;
      foreach ($athletes as $athlete) {
          $x = 49 + ($cols * 57);
          $y = $startY + ($row * ($cellHeight + 41.5));
  
          // Draw athlete's image
          $this::Image($athlete['Picture'], $x - 3, $y - 20, 46, 46);
          $this::Rect($x - 3, $y - 20, 46, 46); // Image border
  
          // Set font and position for name
          $this::SetFont('calibrib', '', 11);
          $this::SetXY($x, $y + 26.4); // Always start at the same Y position
  
          // Get name width
          $name = strtoupper($athlete['FullName']);
          $maxWidth = 40; // Maximum width for a single line
          $nameWidth = $this::GetStringWidth($name);
  
          if ($nameWidth > $maxWidth) {
              // If name is too long, wrap text
              $this::MultiCell($maxWidth, 5, $name, 0, 'C');
          } else {
              // If name is short, keep it on one line at (X, Y + 26.4)
              $this::SetFont('calibrib', '', 12);
              $this::Cell($maxWidth, 10, $name, 0, 0, 'C');
          }
  
          // Draw date of birth (Fixed Position)
          $this::SetXY($x, $y + 33); // Always at original Y position
          $this::SetFont('calibri', '', 11);
          $this::Cell(40, 10, ($athlete['DOB'] ?? 'N/A'), 0, 0, 'C');
  
          // Draw course & year (Fixed Position)
          $this::SetXY($x, $y + 37.6); // Always at original Y position
          $this::SetFont('calibri', '', 11);
          $this::Cell(40, 10, ($athlete['CourseYear'] ?? 'N/A'), 0, 0, 'C');
  
          // Move to next column
          $cols++;
          if ($cols >= $numColumns) {
              $cols = 0;
              $row++;
          }
      }
  }
  
  private function drawCoaches($startY, $cellHeight, $numColumns, $coaches, $includeCoach = false)
  {
    $cols = 0;
    $row = 0;
    // $startY += 32;

    if($includeCoach){
        foreach ($coaches as $coach) {
        // dd($athlete);
        $x = 49 + ($cols * 57);
        $y = $startY - ($row * ($cellHeight + 41.5));
        $this::Image($coach['Picture'], $x - 3, $y - 20, 46, 46);
        $this::Rect($x - 3, $y - 20, 46, 46); //Image border
        // Draw full name
          // Set font and position for name
          $this::SetFont('calibrib', '', 11);
          $this::SetXY($x, $y + 26.4); // Always start at the same Y position
  
          // Get name width
          $name = strtoupper($coach['FullName']);
          $maxWidth = 40; // Maximum width for a single line
          $nameWidth = $this::GetStringWidth($name);
  
          if ($nameWidth > $maxWidth) {
              // If name is too long, wrap text
              $this::MultiCell($maxWidth, 5, $name, 0, 'C');
          } else {
              // If name is short, keep it on one line at (X, Y + 26.4)
              $this::SetFont('calibrib', '', 12);
              $this::Cell($maxWidth, 10, $name, 0, 0, 'C');
          }

        // Draw date of birth
        $this::SetXY($x, $y + 32.5);
        $this::SetFont('calibri', '', 9);
        $this::Cell(40, 10, ($coach['Email'] ?? 'N/A'), 0, 0, 'C');

        // Draw course & year
        $this::SetXY($x, $y + 37.6);
        $this::SetFont('calibri', '', 11);
        $this::Cell(40, 10, ($coach['ContactNo'] ?? 'N/A'), 0, 0, 'C');
        
        // Move to next column
        $cols++;
        // If 5 columns are filled, reset column and move to next row
        if ($cols >= $numColumns) {
            $cols = 0;
            $row++;
        }
    }
    }

  }

  public function Body() {
      $startY = 68;
      $cellHeight = 30;
      $numColumns = 5;
      $verticalHeight = 8 * 10.3; // Using 8 as the base number of rows
      $pageHeight = 297;
  
      $athletes = $this->listAthletes;
      $coaches = $this->listCoaches;
      $numAthletes = count($athletes);
      $numCoaches = count($coaches);
      $remainingAthletes = $numAthletes;
      $remainingCoaches = $numCoaches;
      $offsetY = $startY; // Initial Y position

      // Process athletes first
      while ($remainingAthletes > 0) {
          // Check if new page is needed
          if ($offsetY + $verticalHeight > $pageHeight - 20) {
              $this::AddPage();
              $offsetY = $startY; // Reset to the first-page starting Y
          }
  
          // Determine batch size for athletes
          $athleteCurrentBatch = min($remainingAthletes, $numColumns);

          // Draw athletes' headers and details
          $this->drawHeaders($offsetY - 13, $cellHeight, $athleteCurrentBatch, false, 0);
          $this->drawLines($offsetY - 20, $numColumns, $verticalHeight - 11);
          $this->drawVertical($offsetY - 20, $numColumns, $verticalHeight - 11);
          $this->drawLabels($offsetY + 7, 'default');
          $this->drawAthletes($offsetY + 7, $cellHeight, $numColumns, array_slice($athletes, $numAthletes - $remainingAthletes, $athleteCurrentBatch));
          $remainingAthletes -= $athleteCurrentBatch;
          $offsetY += ($verticalHeight - 11);

          if ($remainingAthletes > 0) {
            $this::Image(GENERAL::PASUCLogo(), 7.5, 135, 30);
        }
      }
  
      // Process coaches after athletes
      while ($remainingCoaches > 0) {
          // Check if new page is needed
          if ($offsetY + $verticalHeight > $pageHeight - 20) {
              $this::AddPage();
              $offsetY = $startY; // Reset to the first-page starting Y
          }
  
          // Determine batch size for coaches
          $coachCurrentBatch = min($remainingCoaches, $numColumns);
  
          // Draw coaches' headers and details
          $this->drawHeaders($offsetY - 13, $cellHeight, 0, true, $coachCurrentBatch);
          $this->drawLines($offsetY - 20, $numColumns, $verticalHeight - 11, true, $coachCurrentBatch);
          $this->drawVertical($offsetY - 20, $numColumns, $verticalHeight - 11);
          $this->drawLabels($offsetY + 7, 'alternative');
          $this->drawCoaches($offsetY + 7, $cellHeight, $numColumns, array_slice($coaches, $numCoaches - $remainingCoaches, $coachCurrentBatch), true);
          $remainingCoaches -= $coachCurrentBatch;
          $offsetY += ($verticalHeight - 11);
          
          if ($remainingCoaches <= 0) {
            $this::Image(GENERAL::PASUCLogo(), 7.5, 135, 30);
        }
      }
  }

        /**
   * Get the value of data
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * Set the value of data
   *
   * @return  self
   */
  public function setDate($date)
  {
    $this->date = $date;

    return $this;
  }

  /**
   * Get the value of id
   */
  public function getScreen()
  {
    return $this->screen;
  }

  /**
   * Set the value of id
   *
   * @return  self
   */
  public function setScreen($screen)
  {
    $this->screen = $screen;

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
  public function getEvent()
  {
    return $this->event;
  }

  /**
   * Set the value of sem
   *
   * @return  self
   */
  public function setEvent($event)
  {
    $this->event = $event;

    return $this;
  }

    /**
   * Get the value of sem
   */
  public function getGender()
  {
    return $this->gender;
  }

  /**
   * Set the value of sem
   *
   * @return  self
   */
  public function setGender($gender)
  {
    $this->gender = $gender;

    return $this;
  }

      /**
   * Get the value of sem
   */
  public function getSport()
  {
    return $this->sport;
  }

  /**
   * Set the value of sem
   *
   * @return  self
   */
  public function setSport($sport)
  {
    $this->sport = $sport;

    return $this;
  }
}