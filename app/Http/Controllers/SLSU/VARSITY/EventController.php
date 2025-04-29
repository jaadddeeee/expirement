<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VARSITY\Event;
use Illuminate\Contracts\Encryption\DecryptException;
use Exception;
use Crypt;
use GENERAL;

class EventController extends Controller
{
    public function index(){
        $events = Event::whereNull('deleted_at')->get();

        $pageTitle = "Manage Event";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        return view('slsu.varsity.VAR_event.event',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'events' => $events
            ]);
    }

    public function save(Request $request){
      try {
        $event = $request->event ?? throw new Exception("Empty Event");
        $existEvent = Event::where("event", $event)->first();

        if($existEvent && $event == $existEvent->event){
          if($existEvent->deleted_at !== null){
            Event::where('id', $existEvent->id)->update(['deleted_at' => null]);
            return response()->json(['Error' => 0, "Message" => trim($event). " successfully restored." ]);
          }else {
            throw new Exception("Event already exists");
          }
        }

        if(!str_contains(strtolower($event), 'men') && !str_contains(strtolower($event),'women')){
          throw new Exception("The event name must contain 'Men' or 'Women'");
        }

        $data = [
          'event' => trim($event)
        ];
  
        $ins = Event::create($data);
        if ($ins)
          return response()->json(['Error' => 0, "Message" => trim($event). " successfully Inserted." ]);
  
        return response()->json(['Error' => \GENERAL::Error("Unable to insert event")], 400 );
      }
      catch(Exception $e){
        return response()->json(['Error' => \GENERAL::Error($e->getMessage(),$e->getFile())], 400);
      }
    }
    
    public function edit(Request $request){
      try{
        $id = Crypt::decryptstring($request->id); 

        $event = Event::findOrFail($id);

        return response()->json($event);
      }catch(Exception $e){
        return response()->json(['errors' => $e->getMessage()], 400);
      }catch(DecryptException $e){
        return response()->json(['errors' => $e->getMessage()], 400);
      }
    }

    public function update(Request $request) {
      try {
          // Decrypt the ID from the request
          
          $decryptedId = Crypt::decryptString($request->hiddentID);
          // Find the event by ID
          $event = Event::findOrFail($decryptedId);
          $upEvent = $request->updateEvent;

          $existEvent = Event::where("event", $event)->first();
  
          // Validate input
          if (!$upEvent || empty($upEvent)) 
            throw new Exception("Event name cannot be empty");

            // Trim input event name
          $newEventName = trim($upEvent);

          // Check if there's any change before updating
          if ($event->event === $newEventName) 
            throw new Exception("No changes detected");
  
          // Update event data
          $event->event = trim($request->updateEvent);
          $updated = $event->save(); // Save the changes
  
          if ($updated) {
              return response()->json(['Error' => 0, "Message" => \GENERAL::Success("Event successfully updated")]);
          }
  
          return response()->json(['Error' => 1, "Message" => "Unable to update event"]);
          
      } catch (DecryptException $e) {
          return response()->json(['Error' => \GENERAL::Error('Invalid Event ID')], 400);
      } catch (Exception $e) {
          return response()->json(['Error' => \GENERAL::Error($e->getMessage())], 400);
      }
  }

  public function destroyEvent(Request $request){
    try{
      $id = Crypt::decryptString($request->id); 
      // dd($id);
      $event = Event::findOrFail($id);

      if($event->varsities()->exists() || $event->coaches()->exists()){
        throw new Exception("Unable to delete ". $event->event .". Event is still in active.");
      }

      $event->delete();
      return response()->json(['Errors' => 0, "Message" => "Event successfully deleted"]);
    }catch(Exception $e){
      return response()->json(['Errors' => GENERAL::Error($e->getMessage())], 400);
    }catch(DecryptException $e){
      return response()->json(['Errors' => 'Invalid Event ID'], 400);
    }
  }
  
}
