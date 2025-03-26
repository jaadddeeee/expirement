<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;
use App\Models\VARSITY\Scuaa;
use App\Models\VARSITY\ListVarsity;

class ScuaaReport extends TCPDF
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

  private function generate()
  {
    $query = ListVarsity::with(['event' => function ($query) {
      $query->whereNull('deleted_at'); // Ensures only active events are included
    }])
    ->whereNull('deleted_at')
    ->orderBy('SchoolYear', 'desc')
    ->get(); // Fetch all records without pagination
  
    // Fetch student data from multiple databases (batch query)
    $connections = ['sg', 'mcc', 'to', 'bn', 'sj', 'hn'];
    $studentNos = $query->pluck('StudentNo')->unique(); // Get unique Student Numbers once
    $allStudents = collect();
    
    foreach ($connections as $connection) {
        $students = Student::on($connection)
            ->whereIn('StudentNo', $studentNos)
            ->get();
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
    
        return [
            'SchoolYear' => $item->SchoolYear,
            'StudentNo'  => $item->StudentNo,
            'FullName'  => $fullName,
            'DOB'        => $dob,
            'CourseYear' => isset($student->Course, $student->StudentYear) 
                              ? "{$student->Course} - {$student->StudentYear}" 
                              : 'N/A',
            'Picture'    => $student->Picture ?? null,
            'event_name' => optional($item->event)->event ?? 'N/A', // Safe handling
        ];
    })->toArray();
  
    return $varsityList; // Return as a list (array)

  }
  
  public function Header(){
    $startYear = date('Y'); // Get the current year

    // Fetch the ScuaaLogo where the Date column contains the current year
    $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $startYear . '%')
        ->select('*')
        ->first();

      $this->varsityList = $this->generate();
      $this->letter->ScuaaHeaderLandScape();
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
      $this::setXY(73, $startY);
      $this::SetFont('lucidafax','BU',14);
      $this::Cell(1,5,strtoupper($scuaaList->University),0,1,'C');
  }

  private function drawHeaders($startY, $cellHeight, $numColumns, $includeCoach = false) {
      for ($col = 0; $col < $numColumns; $col++) {
          $this::SetXY(49 + ($col * 57), $startY - $cellHeight);
          $this::SetFont('calibrib', '', 12);
          $this::Cell(40, 51, 'ATHLETES', 0, 0, 'C');
      }
  
      // Append "Coach" header if needed
      if ($includeCoach) {
          $this::SetXY(49 + ($numColumns * 57), $startY - $cellHeight);
          $this::SetFont('calibrib', '', 12);
          $this::Cell(40, 51, 'COACH', 0, 0, 'C');
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

  private function drawNames($startY, $type = 'default') {
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
        // Draw full name
        $this::SetXY($x, $y + 26.4);
        $this::SetFont('calibrib', '', 12);
        $this::Cell(40, 10, $athlete['FullName'], 0, 0, 'C');

        // Draw date of birth
        $this::SetXY($x, $y + 33);
        $this::SetFont('calibri', '', 11);
        $this::Cell(40, 10, ($athlete['DOB'] ?? 'N/A'), 0, 0, 'C');

        // Draw course & year
        $this::SetXY($x, $y + 37.6);
        $this::SetFont('calibri', '', 11);
        $this::Cell(40, 10, ($athlete['CourseYear'] ?? 'N/A'), 0, 0, 'C');
        
        // Move to next column
        $cols++;
        // If 5 columns are filled, reset column and move to next row
        if ($cols >= $numColumns) {
            $cols = 0;
            $row++;
        }
    }
  }

  public function Body() {
    $startY = 68;
    $cellHeight = 30;
    $numColumns = 5;
    $verticalHeight = 8 * 10.3; // Using 8 as the base number of rows
    $pageHeight = 297;

    $athletes = $this->varsityList;
    $numAthletes = count($athletes);
    $remainingAthletes = $numAthletes;
    $offsetY = $startY; // Initial Y position

    while ($remainingAthletes > 0) {
      // Check if new page is needed
      if ($offsetY + $verticalHeight > $pageHeight - 20) {
          $this::AddPage();
          $offsetY = $startY; // Reset to the first-page starting Y
      }
  
      // Determine batch size
      $currentBatch = min($remainingAthletes, $numColumns);
      $includeCoach = ($remainingAthletes < 5);

      // Draw table elements for the current batch
      $this->drawHeaders($offsetY - 13, $cellHeight, $currentBatch, $includeCoach);
      $this->drawLines($offsetY - 20, $numColumns, $verticalHeight - 11);
      $this->drawVertical($offsetY - 20, $numColumns, $verticalHeight - 11);
  
      // Draw names and elements for the current batch
      if ($includeCoach) {
          $this->drawNames($offsetY + 7, 'alternative');
      } else {
          $this->drawNames($offsetY + 7, 'default');
      }
  
      // Draw athletes properly
      $this->drawAthletes($offsetY + 7, $cellHeight, $numColumns, array_slice($athletes, $numAthletes - $remainingAthletes, $currentBatch));
  
      // Move Y position for the next batch
      $remainingAthletes -= $numColumns; 
      $offsetY += ($verticalHeight - 11);
  }
  
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