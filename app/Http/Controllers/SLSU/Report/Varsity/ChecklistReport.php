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


class ChecklistReport extends TCPDF
{
    protected $id;
    protected $sy;
    protected $event;
    protected $lists;
    protected $prefs;
    protected $pref;
    public function __construct(){
        $this->letter = new LetterHead();
        $this->prefs = new Preference();
        $this->pref = $this->prefs->GetDefaults();
        $this->caption = [
            'NAME OF ATHLETES (LAST NAME,FIRST NAME, M.I)','AGE','DATE OF BIRTH','FORM 2','FORM 3',
            'TOR','PSA B.C','REMARKS'
        ];
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
        
            return [
                'SchoolYear' => $item->SchoolYear,
                'StudentNo'  => $item->StudentNo,
                'FullName'  => strtoupper($fullName),
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

            $fullName = ($emp->FirstName ?? 'N/A')  . (!empty($emp->MiddleName) ? ' ' . $emp->MiddleName[0] . '. ' : '') .
            ($emp->LastName ?? 'N/A');

            $campus = $emp->Campus ?? null;
            $folder = $campusToFolder[$campus] ?? 'UNKNOWN';

            $picture = ('storage/' . $coach->Picture);
    
            return [
                'SchoolYear' => $item->SchoolYear,
                'EmpID'      => $item->CoachID,
                'Email'     => $emp->EmailAddress ?? null,
                'ContactNo'  => $emp->Cellphone ?? null,
                'FullName'  => strtoupper($fullName) ?? null,
                'Picture'    => $picture ?? null,
                'event_name' => optional($item->event)->event ?? null,
            ];
        });

        $listCoaches = $coachList->sortBy('FullName')->values()->toArray();

        // dd($listCoaches);
    
        return $listCoaches; // Return the transformed list
    }

    public function Header()
    {
        $startYear = date('Y'); // Get the current year
    
        // Fetch the ScuaaLogo where the Date column contains the current year
        $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $startYear . '%')
            ->select('*')
            ->first();
    
        $this->listAthletes = $this->listVarsity();
        $this->listCoaches = $this->listCoaches();
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

        $this::setXY(304, 12);
        $this::SetFont('calibri','',10);
        $this::Cell(0,5,'SCUAA Form 1',0,1,'L');

        $startY += 9.4;
        $ctr = 0;
    
        // Adjust the starting X position and spacing for captions
        $cWidth = 15; // Starting X position
        $spacing = [47.5, 25.8, 30, 22, 19, 19, 60, 100]; // Define custom widths for each column
    
        $this::SetFont('calibri', '', 11);
        foreach ($this->caption as $index => $caption) {
            $this::setXY($cWidth, $startY);
            $this::Cell(75, 3, $caption, 0, 0, 'C');
            $cWidth += $spacing[$index]; // Increment X position by the column width
        }
    }

    public function Footer()
    {
        // Set the starting and ending positions for the horizontal line
        $startX = 5;
        $endX = 325;
        $yPosition = 190;
        $cols = 0;

        // Draw a horizontal line
        $this::Line($startX, $yPosition, $endX, $yPosition);

        // Set the positions for the vertical lines
        $xPositions = [5, 94, 106, 145, 167, 189, 205, 226.8, 325];
        $startY = 190; 
        $endY = 46;   

        foreach ($xPositions as $index => $x) {
            // Skip vertical lines for Age, Date of Birth, Form 2, Form 3, and TOR
            if (in_array($x, [$xPositions[1],$xPositions[2], $xPositions[3], $xPositions[4], $xPositions[5], $xPositions[6]])) {
                continue;
            }

            if($index === 7){
                $this::setLineWidth(0.4);
            }else{
                $this::setLineWidth(0);
            }
            $this::Line($x, $startY, $x, $endY);
        }

        foreach ($this->listCoaches as $coach) {
            $this::SetFont('calibrib', 'B', 12); 
            $this::setXY(13 + ($cols * 80), $startY - 33); 
            $this::Cell(40, 51, $coach['FullName'], 0, 0, 'C');
            $cols++;
        }

        $this::SetXY(256, $startY - 33);
        $this::SetFont('calibriB', '', 12);
        $this::Cell(40, 51,strtoupper($this->prefs->GetDefaultValue($this->pref, "SportsDirector")), 0, 0, 'C');
        //label
        $this::SetXY(256, $startY - 28);
        $this::SetFont('calibri', '', 12);
        $this::Cell(40, 51, 'Name & Signature of Sport Director', 0, 0, 'C');

        //label
        for ($col = 0; $col < 3; $col++) {
            $this::SetXY(13 + ($col * 80), $startY - 28);
            $this::SetFont('calibri', '', 12);
            $this::Cell(40, 51, 'Name & Signature of Coach', 0, 0, 'C');
            
        }
    }
    
    private function headerLine()
    {
        $xPositions = [5, 94, 106, 145, 167, 189, 205, 226.8, 325];

        $startY = 46;
        $endY = 53; 

        foreach ($xPositions as $x) {
            $this::Line($x, $startY, $x, $endY);
        }
        $this::Line(5, $startY, 325, $startY); // Top line
        $this::Line(5, $endY, 325, $endY); // Bottom line

    }

    private function drawCategory()
    {
        $event = $this->listAthletes[0] ?? null;
    
        $date = date('F j, Y');
    
        $this::SetFont('calibri','',12);
        $this::setXY(295, 32);
        $this::Cell(1,5,$date,0,0,'C');
        $this::Line(266, 37, 325, 37);
    
        $this::setXY(295, 39);
        $this::SetFont('calibrib','',14);
        $this::Cell(1,5,$event['gender'],0,0,'C');
        $this::Line(266, 44, 325, 44);
    
        $this::SetFont('lucidafax','B',12);
        $this::setXY(265, 25);
        $this::Cell(1,5,$event['event_name'],0,0,'C');
    }

    public function Body()
    {
        $this->headerLine(); 
        $this->drawCategory(); 

        $startY = 53; 
        $rowHeight = 7; 
        $xPositions = [5, 94, 106, 145, 167, 189, 205, 226.8, 325];

        // Loop through the athletes and render their details
        foreach ($this->listAthletes as $index => $athlete) {
            // Draw horizontal line for the row
            $this::Line(5, $startY, 325, $startY);


            $this::SetFont('calibri', '', 11); 
            $this::setXY($xPositions[0] + 2, $startY + 1.5); 
            $this::Cell(10, $rowHeight - 2, ($index + 1) . '.', 0, 0, 'L'); 

            $this::SetFont('calibrib', 'B', 12); 
            $this::setXY($xPositions[0] + 12, $startY + 1.5); 
            $this::Cell($xPositions[1] - $xPositions[0] - 14, $rowHeight - 2, $athlete['FullName'], 0, 0, 'L');

            $this::SetFont('calibri', '', 11);
            $this::setXY($xPositions[1] + 2, $startY + 1.5);
            $age = $athlete['DOB'] !== 'N/A' ? date_diff(date_create($athlete['DOB']), date_create('today'))->y : 'N/A';
            $this::Cell($xPositions[2] - $xPositions[1] - 4, $rowHeight - 2, $age, 0, 0, 'C');

            $this::SetFont('calibrib', '', 11);
            $this::setXY($xPositions[2] + 2, $startY + 1.5);
            $this::Cell($xPositions[3] - $xPositions[2] - 4, $rowHeight - 2, $athlete['DOB'], 0, 0, 'C');

            // Increment Y position for the next row
            $startY += $rowHeight;
        }

        // Draw the final horizontal line at the bottom
        $this::Line(5, $startY, 325, $startY);

        $startY += $rowHeight; 

        // Draw vertical lines
        foreach ($xPositions as $x) {
            $this::Line($x, 46, $x, $startY);
        }

        $this::SetFont('calibri', '', 11); 
        $this::setXY($xPositions[0], $startY - 5.6); 
        $this::Cell(89, $rowHeight - 2, '---nothing follows---', 0, 0, 'C'); 
        
        $this::SetLineWidth(0.4);
        $this::Line(5, $startY, 325, $startY);
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
}
