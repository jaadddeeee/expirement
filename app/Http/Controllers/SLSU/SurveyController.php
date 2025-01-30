<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Exception;
use GENERAL;
use Crypt;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Answer;

class SurveyController extends Controller
{
    //
    // Display survey creation form

    public function index(){
      $pageTitle = "Survey Masterlists";
      $headerAction = '
          <a href="javascript:history.back()" class="btn btn-sm btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalSurvey">New Survey</a>';

      return view('surveys.index',[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function dataonly(){

      $surveys = Survey::orderby("title", "ASC")->get();
      return view('surveys.dataonly', compact('surveys'));
    }

    // Store the survey with questions and possible answers
    public function store(Request $request)
    {

      try{

        $request->validate([
          'title' => 'required',
          'date_start' => 'required',
          'date_end' => 'required',
          'description' => 'required',
        ]);

        $survey = Survey::create($request->all());

      }catch(Exception $e){
        return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
      }catch(DecryptException $e){
        return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
      }

    }

    public function storequestion(Request $request)
    {

      try{

        $sid = Crypt::decryptstring($request->hiddenSurveyID);

        $request->validate([
          'question' => 'required',
          'type' => 'required'
        ]);

        if (strtolower($request->type) == "radio" or strtolower($request->type) == "checkbox" ){
            $wala = true;
            $ans = [];
            for($x=1;$x<=10;$x++){
                $num = "option".$x;
                if (!empty($request->$num)){
                  $wala = false;
                }
            }

            if ($wala){
              throw new Exception("Please enter value in option");
            }
        }

        $datainQ = [
          'survey_id' => $sid,
          'question' => $request->question,
          'type' => strtolower($request->type)
        ];

        $qID = Question::insertGetId($datainQ);

        if (!empty($qID)){
          $ans = [];
          if (strtolower($request->type) == "radio" or strtolower($request->type) == "checkbox" ){

              for($x=1;$x<=10;$x++){
                  $num = "option".$x;
                  if (!empty($request->$num)){
                    array_push($ans, [
                      'question_id' => $qID,
                      'answer' =>$request->$num
                    ]);
                  }
              }

          }
          // dd($ans);
          $ansSave = Answer::insert($ans);
          if (!$ansSave){
            throw new Exception("Please enter value in option");
          }
        }

      }catch(Exception $e){
        return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
      }catch(DecryptException $e){
        return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
      }

    }

    // Display a specific survey
    public function show(Request $request)
    {

        try{

          $id = Crypt::decryptstring($request->id);
          $survey = Survey::find($id);

          if (empty($survey))
            throw new Exception("Invalid survey");


          $pageTitle = $survey->title;
          $headerAction = '
              <a href="javascript:history.back()" class="btn btn-sm btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalSurvey">New Survey</a>';

          return view('surveys.show', compact('survey'),[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'id' => $request->id
          ]);

        }catch(Exception $e){
          return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
        }catch(DecryptException $e){
          return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
        }

    }

    public function results(Request $request){
      try{

        $id = Crypt::decryptstring($request->id);
        $survey = Survey::find($id);

        if (empty($survey))
          throw new Exception("Invalid survey");


        $pageTitle = $survey->title;
        $headerAction = '';

        $respondents = $this->getRespondents($id);
        $studentinfo = $this->getPersonalInfo($respondents->pluck('student_id')->toArray());

        return view('surveys.results', compact('survey','respondents','studentinfo'),[
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'id' => $request->id
        ]);

      }catch(Exception $e){
        return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
      }catch(DecryptException $e){
        return response()->json(['errors' => GENERAL::Error("Line ".$e->getLine().': '.$e->getMessage())],400);
      }
    }

    public function getRespondents($sid){

        $res = DB::connection('evaluation')
          ->table('responses as r')
          ->select("r.student_id")
          ->where("survey_id", $sid)
          ->where('campus', session('campus'))
          ->groupby("r.student_id")
          ->get();
          // dd($res);
        return $res;
    }

    public function getPersonalInfo($sid){
        $res = DB::connection(strtolower(session('campus')))
          ->table('students as s')
          ->select("s.*", "c.accro", 'm.course_major')
          ->leftjoin('course as c','s.Course', '=', 'c.id')
          ->leftjoin('major as m','s.major', '=', 'm.id')
          ->whereIn("s.StudentNo", $sid)
          ->get();
          // dd($res);
        return $res;
    }

    public function getResponses($data){
        $res = DB::connection('evaluation')
        ->table('responses as r')
        ->select('r.*','a.answer')
        ->leftjoin("answers as a", "r.response", '=', 'a.id')
        ->where("survey_id", $data['SID'])
        ->where("student_id", $data['StudentID'])
        ->get();
        return $res;
    }
}
