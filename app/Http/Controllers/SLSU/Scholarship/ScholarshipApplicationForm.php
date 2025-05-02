<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Contracts\Encryption\DecryptException;

use Illuminate\Support\Facades\DB;
use DateTime;



use GENERAL;

class ScholarshipApplicationForm extends TCPDF
{
    protected $id, $letter, $studentName, $studentName2, $semester, $schoolYear;

    // Student Info
    protected $studentNo, $email, $contactNo, $age, $birthDate, $birthPlace, $sex, $civilStatus, $citizenship, $motherName, $fatherName, $motherOccu, $fatherOccu, $picture;

    // Academic Info
    protected $course, $major, $section, $scholarship, $schEnrollmentId, $scholarshipAcronym, $elemSchool, $highSchool;

    // Address
    protected $p_address, $zipCode;

    public function __construct()
    {
        $this->letter = new LetterHead();
    }

    public function generate()
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                throw new \Exception('Invalid campus database connection');
            }

            $scholar = DB::connection($campus)
                ->table('sch_scholar_details')
                ->join('students', 'sch_scholar_details.student_no', '=', 'students.StudentNo')
                ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
                ->join('sch_scholarships', 'sch_scholar_details.scholarship_id', '=', 'sch_scholarships.id')
                ->where('sch_scholar_details.id', $this->getId())
                ->where('sch_scholar_enrollments.id', $this->getSchEnrollmentId())
                ->where('sch_scholar_enrollments.school_year', $this->getSchoolYear())
                ->where('sch_scholar_enrollments.semester', $this->getSemester())
                ->select(
                    'sch_scholar_details.*',
                    'students.FirstName',
                    'students.MiddleName',
                    'students.LastName',
                    'students.Course',
                    'students.StudentYear',
                    'students.major',
                    'students.Section',
                    'students.ContactNo',
                    'students.email',
                    'students.BirthDate',
                    'students.Birthplace',
                    'students.Sex',
                    'students.civil_status',
                    'students.nationality',
                    'students.mother_name',
                    'students.mother_occu',
                    'students.father_name',
                    'students.father_occu',
                    'students.p_street',
                    'students.p_municipality',
                    'students.p_province',
                    'students.p_zip',
                    'students.Picture',
                    'students.elem_school',
                    'students.elem_year',
                    'students.high_school',
                    'students.high_year',
                    'sch_scholarships.sch_name AS scholarship_name',
                    'sch_scholarships.sch_acronym AS scholarship_acronym',
                    'sch_scholar_enrollments.id AS enrollment_id',
                    'sch_scholar_enrollments.semester',
                    'sch_scholar_enrollments.school_year'
                )
                ->first();

            if (!$scholar) {
                throw new \Exception('Scholar not found');
            }

            // calculate age
            if (empty($scholar->BirthDate)) {
                $age = 0;
            } else {
                $from = new DateTime($scholar->BirthDate);
                $to = new DateTime('today');
                $age = $from->diff($to)->y;
            }

            // sch_scholarships table
            $this->scholarship = $scholar->scholarship_name ?? 'N/A';
            $this->scholarshipAcronym = $scholar->scholarship_acronym ?? 'N/A';

            // sch_scholar_enrollments table
            $this->semester = GENERAL::Semesters()[$scholar->semester]['Short'];
            $this->schoolYear = GENERAL::setSchoolYearLabel($scholar->school_year, $scholar->semester);

            // students table
            $this->studentNo = $scholar->student_no;
            $this->studentName = $scholar->LastName . ', ' . $scholar->FirstName . ' ' . $scholar->MiddleName;
            $this->studentName2 = $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '. ' : '') . $scholar->LastName;
            $this->sex = $scholar->Sex;
            $this->course = $scholar->Course;
            $this->section = $scholar->StudentYear . ' - ' . $scholar->Section;
            $this->birthDate = $scholar->BirthDate ? date('F j, Y', strtotime($scholar->BirthDate)) : 'N/A';
            $this->birthPlace = $scholar->Birthplace;
            $this->major = $scholar->major ?? 'N/A';
            $this->motherName = $scholar->mother_name;
            $this->motherOccu = $scholar->mother_occu;
            $this->fatherName = $scholar->father_name;
            $this->fatherOccu = $scholar->father_occu;
            $this->contactNo = $scholar->ContactNo;
            $this->email = $scholar->email;
            $this->age = $age;
            $this->p_address = $scholar->p_street . ', ' . $scholar->p_municipality . ', ' . $scholar->p_province;
            $this->zipCode = $scholar->p_zip;
            $this->citizenship = $scholar->nationality;
            $this->civilStatus = $scholar->civil_status;
            $this->picture = $scholar->Picture;
            $this->elemSchool = $scholar->elem_school . ', ' . $scholar->elem_year;
            $this->highSchool = $scholar->high_school . ', ' . $scholar->high_year;
        } catch (DecryptException $e) {
            throw new \Exception('Invalid or corrupted scholarship ID');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function Header()
    {
        $this->letter->ReportHeader();
    }

    public function content()
    {
        $this->generate();

        $startY = 45;

        $leftMargin = 25.4; // 1 inch
        $rightMargin = 25.4;
        $pageWidth = $this::GetPageWidth();
        $usableWidth = $pageWidth - $leftMargin - $rightMargin;

        $title = '<p style="font-family: cambria; font-size: 11px;">APPLICATION FOR ' . $this->scholarshipAcronym . ' SCHOLARSHIP</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $title, 0, 1, false, true, 'C');

        $startY += 15;

        $details = '<table cellpadding="1" cellspacing="0" style="width: 100%; font-family: cambria; font-size: 10px;">
                    <tr>
                        <td style="width: 50%;">Course: <strong>' . $this->course . '</strong></td>
                        <td style="width: 50%;">Date Applied: _______________</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">Major: <strong>' . $this->major . '</strong></td>
                        <td style="width: 50%;">Gen. Ave.: ___________________</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">Yr. & Sec.: <strong>' . $this->section . '</strong></td>
                        <td style="width: 50%;">Date Complied: _____________</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">Student No: <strong>' . $this->studentNo . '</strong></td>
                        <td style="width: 50%;">SOP Receipt #: ______________</td>
                    </tr>
                 </table>
                 <p style="font-family: cambria; font-size: 11px;"><strong>I. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; PERSONAL DATA</strong></p>
                 <p style="font-family: cambria; font-size: 10px;"><strong>' . $this->studentName . '</strong>
                 <br><br><br>Home Address: <strong>' . $this->p_address . '</strong></p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $details, 0, 1, false, true, 'L');

        $familyBackground = '<p style="font-family: cambria; font-size: 11px;"><strong>II. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; FAMILY BACKGROUND</strong></p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY + 61, $familyBackground, 0, 1, false, true, 'L');

        $familyBDetails = '<p style="font-family: cambria; font-size: 10px;">Father: <strong>' . $this->fatherName . '</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Address: <strong>address data diri</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Occup: <strong>' . $this->fatherOccu . '</strong>
        <br>Educational Attainment: _____ Elementary _____ High School _____ College
        <br>Mother: <strong>' . $this->motherName . '</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Address: <strong>address data diri</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Occup: <strong>' . $this->motherOccu . '</strong>
        <br>Educational Attainment: _____ Elementary _____ High School _____ College
        <br>Total Family Members:_____ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; # of married brod.:_____ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; # of married sis.:_____ 
        <br># of unmarried brod.:_____ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; # of unmarried sis.:_____ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; your rank:_____</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY + 67, $familyBDetails, 0, 1, false, true, 'L');

        $scholasticsInterest = '<p style="font-family: cambria; font-size: 11px;"><strong>III. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SCHOLASTICS RECORDS & INTEREST:</strong></p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY + 97, $scholasticsInterest, 0, 1, false, true, 'L');

        $scholasticsInterestDetails = '<p style="font-family: cambria; font-size: 10px; text-decoration: underline;">EDUC\'L ATT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        NAME OF SCHOOL &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        YEAR GRADUATED</p>
        <p style="font-family: cambria; font-size: 10px;">Elementary: <strong>' . $this->elemSchool . '</strong>
        <br>High School: <strong>' . $this->highSchool . '</strong>
        <br>FOR TRANSFEREE ONLY
        <br>Last School Attended:______________________________________________________________________________________________
        <br><br><span style="font-family: cambria; font-size: 11px;"><strong>IV. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SOCIAL ATTENDED</strong></span>
        <br><br>Are you a frat member? No_____  Yes_____. If yes, please specify ________________________________________________
        <br>What school activity/ies you wish to participate? (Check one or more) Sports_____ Science Club_____ English Club_____, School Publication_____, Glee Club_____, School Politics_____, Others, please specify:______
        <br><br><span style="font-family: cambria; font-size: 11px;"><strong>V. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; HEALTH INFORMATIONS</strong></span>
        <br><br>Height_____  wt_____ Visions:w/glass_____ normal_____ Hearing:ok_____ not ok_____ Physical handicap? No_____ yes_____. If yes what?
        <br>Please specify:____________________________
        <br><br><span style="font-family: cambria; font-size: 11px;"><strong>VI. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SOURCE OF INCOME</strong></span>
        <br><br>_____farming, _____business, _____employment: Government_____ Private_____
        </p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY + 105, $scholasticsInterestDetails, 0, 1, false, true, 'L');

        $startY += 36;
        $this::setXY($leftMargin, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(180, 4, "_______________________________________________________", 0, 0, 'L');

        $this::setXY($leftMargin + 92, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(180, 4, "{$this->sex}", 0, 0, 'L');

        $this::setXY($leftMargin + 105, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(180, 4, "{$this->age}", 0, 0, 'L');

        $this::setXY($leftMargin + 119, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(180, 4, "{$this->birthDate}", 0, 0, 'L');

        $this::setXY($leftMargin, $startY + 4);
        $this::SetFont('cambria', '', 9);
        $this::Cell(180, 4, "Family Name              First Name              Middle Name                             Sex             Age                         Birthday", 0, 0, 'L');

        $this::setXY($leftMargin + 88, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(180, 4, "________", 0, 0, 'L');

        $this::setXY($leftMargin + 102, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(180, 4, "________", 0, 0, 'L');

        $this::setXY($leftMargin + 118, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(180, 4, "_________________________", 0, 0, 'L');

        $this::setXY($leftMargin + 23, $startY + 13);
        $this::SetFont('cambria', '', 10);
        $this::Cell(180, 4, "_________________________________________________________________________________________________", 0, 0, 'L');

        $this::setXY($leftMargin + 25, $startY + 17);
        $this::SetFont('cambria', '', 9);
        $this::Cell(180, 4, "   Brgy.                                               Town                                               Province", 0, 0, 'L');


        $picture = public_path('storage/photo/SG/' . $this->picture);

        if (!empty($this->picture) && file_exists($picture)) {
            $this::Image($picture, 155, 38, 50.8, 50.8);
        } else {
            $pictureBoxX = 155;
            $pictureBoxY = 38;
            $pictureBoxSize = 50.8;
            $this::Rect($pictureBoxX, $pictureBoxY, $pictureBoxSize, $pictureBoxSize, 'D');

            $this::setXY($pictureBoxX, $pictureBoxY + ($pictureBoxSize / 2) - 6);
            $this::Cell($pictureBoxSize, 12, "No Image", 0, 0, 'C');
        }

        // 2x2 ID Picture
        $pictureBoxX = 155;
        $pictureBoxY = 38;
        $pictureBoxSize = 50.8;
        $this::Rect($pictureBoxX, $pictureBoxY, $pictureBoxSize, $pictureBoxSize, 'D');


        // PAGE 2
        $this::AddPage();
        // Define margins (1 inch = 25.4 mm)
        $leftMargin = 25.4;
        $rightMargin = 25.4;
        $topMargin = 25.4;
        $bottomMargin = 25.4;

        $this::SetMargins($leftMargin, $topMargin, $rightMargin);
        $this::SetAutoPageBreak(true, $bottomMargin);

        $startY = $topMargin + 12;
        $html = '<p style="font-family: cambria; font-size: 10px;"><span style="font-family: cambria; font-size: 11px;"><strong>VII. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; RESIDENCE & FOLLOW-UPS</strong></span>
                    <br><br>Where are you living while studying in this university? &nbsp;&nbsp;&nbsp;&nbsp; _____boarding house &nbsp;&nbsp; _____own house 
                    <br>_____or with relative
                    <br><br><span style="font-style: italic;">(Note: If you\'re living in a boarding house or with your relative please write down the name and address of your boarding house or relative\'s house.) </span>
                 </p>
                 <table cellpadding="1" cellspacing="0" style="width: 100%; font-family: cambria; font-size: 10px;">
                    <tr>
                        <td style="width: 50%;"><strong>Year Level & School Year</strong></td>
                        <td style="width: 25%;"><strong>First Semester</strong></td>
                        <td style="width: 25%;"><strong>Second Semester</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 40%;">1st year _________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;"></td>
                        <td style="width: 30%;">Address _________________________</td>
                        <td style="width: 30%;">Address _________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;">2nd year ________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;"></td>
                         <td style="width: 30%;">Address _________________________</td>
                        <td style="width: 30%;">Address _________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;">3rd year ________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;"></td>
                        <td style="width: 30%;">Address _________________________</td>
                        <td style="width: 30%;">Address _________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;">4th year ________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;"></td>
                        <td style="width: 30%;">Address _________________________</td>
                        <td style="width: 30%;">Address _________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;">5th year ________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                        <td style="width: 30%;">Name ___________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 40%;"></td>
                         <td style="width: 30%;">Address _________________________</td>
                        <td style="width: 30%;">Address _________________________</td>
                    </tr>
                 </table>
                 <p style="text-align: center; font-size: 11px;"><strong>FOLLOW-UPS</strong></p>';
        $pageWidth = $this::getPageWidth();
        $usableWidth = $pageWidth - $leftMargin - $rightMargin;
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html, 0, 1, false, true, 'L');

        // big box
        $boxX = 25.4; // x position
        $boxY = 139; // y position
        $boxWidth = 160; // width 
        $boxHeight = 100; // height
        $this::Rect($boxX, $boxY, $boxWidth, $boxHeight, 'D');

        $startY = $boxY + 103;
        $html = '<p style="font-family: cambria; font-size: 10px;">Recorder:
        <br><br><br>________________________________
        <br>Scholarship Coordinator</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html, 0, 1, false, true, 'L');
    }

    public function Footer()
    {
        $this->letter->ReportFooter(['QC' => config('QC.SS05')]);
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getSchEnrollmentId()
    {
        return $this->schEnrollmentId;
    }

    public function setSchEnrollmentId($schEnrollmentId)
    {
        $this->schEnrollmentId = $schEnrollmentId;
        return $this;
    }

    public function getStudentName()
    {
        return $this->studentName;
    }


    public function getSemester()
    {
        return $this->semester;
    }

    public function setSemester($semester)
    {
        $this->semester = $semester;
        return $this;
    }

    public function getSchoolYear()
    {
        return $this->schoolYear;
    }

    public function setSchoolYear($schoolYear)
    {
        $this->schoolYear = $schoolYear;
        return $this;
    }
}
