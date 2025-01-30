<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\user;

use GENERAL;

class AccountController extends Controller
{

  public function update(Request $request)
  {
      $currentpassword = $request->currentpassword;
      $newpassword = $request->newpassword;
      $newretypepassword = $request->newretypepassword;

      if (empty($currentpassword))
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error("Empty current password")]);

      if (empty($newpassword))
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error("Empty new password")]);

      if (empty($newretypepassword))
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error("Empty retype new password")]);


      if ($newpassword != $newretypepassword)
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error("Passwords did not matched")]);


      $pword = sha1(trim($currentpassword)."nestnie");

      $oldcheck = User::where("UserName", auth()->user()->UserName)
          ->where("Password", $pword)
          ->first();

      if (empty($oldcheck))
        return response()->json(['Error' => 1, 'Message' => GENERAL::Error("Invalid old password")]);


      $uppercase = preg_match('@[A-Z]@', $newpassword);
      $lowercase = preg_match('@[a-z]@', $newpassword);
      $number    = preg_match('@[0-9]@', $newpassword);
      $specialChars = preg_match('@[^\w]@', $newpassword);

      if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($newpassword) < 8) {
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error('<div class="alert alert-danger" role="alert"><b>Password Change Failed:</b> <br>Password must be at least 8 characters in length.
                      <br>Password must include at least one upper case letter.
                      <br>Password must include at least one number.
                      <br>Password must include at least one special character.
                      </div>')]);
      }

      $npword = sha1(trim($newpassword)."nestnie");

      $up = User::where("UserName", auth()->user()->UserName)
          ->update(["Password" => $npword]);

      if ($up)
        return response()->json(['Error' => 0, 'Message' => GENERAL::Success("Password successfully change")]);

      return response()->json(['Error' => 1, 'Message' => GENERAL::Error("Unable to change password")]);
  }


}
