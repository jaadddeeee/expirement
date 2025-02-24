<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VARSITY\Event;
use Illuminate\Contracts\Encryption\DecryptException;
use Crypt;

class EventController extends Controller
{
    public function index(){
        $events = Event::all();

        $pageTitle = "Manage Event";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        return view('slsu.varsity.VAR_event.event',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'events' => $events
            ]);
    }

    public function save(Request $request){
      $event = $request->event;
      $existEvent = Event::where("event", $event)->first();

      if (empty($event))
        return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Empty Event")]);


      if (!empty($existEvent))
      return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Duplicate entry for event")]);

      $data = [
        'event' => trim($event)
      ];

      $ins = Event::create($data);
      if ($ins)
        return response()->json(['Error' => 0, "Message" => \GENERAL::success(trim($event). " successfully Inserted." )]);

      return response()->json(['Error' => 1, "Message" => "Unable to insert event"]);
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
          if (!$upEvent || empty($upEvent)) {
              return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Event name cannot be empty")]);
          }

            // Trim input event name
          $newEventName = trim($upEvent);

          // Check if there's any change before updating
          if ($event->event === $newEventName) {
              return response()->json(['Error' => 1, "Message" => \GENERAL::Error("No changes detected")]);
          }
  
          // Update event data
          $event->event = trim($request->updateEvent);
          $updated = $event->save(); // Save the changes
  
          if ($updated) {
              return response()->json(['Error' => 0, "Message" => \GENERAL::Success("Event successfully updated")]);
          }
  
          return response()->json(['Error' => 1, "Message" => "Unable to update event"]);
          
      } catch (DecryptException $e) {
          return response()->json(['Error' => 1, 'Message' => 'Invalid Event ID'], 400);
      } catch (Exception $e) {
          return response()->json(['Error' => 1, 'Message' => $e->getMessage()], 400);
      }
  }
  
}
