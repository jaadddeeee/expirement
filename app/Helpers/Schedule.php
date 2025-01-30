<?php

namespace App\Helpers;

use App\Http\Controllers\SLSU\ScheduleController;
use Crypt;

class Schedule {

  private static $id;
  private static $sy;
  private static $sem;
  public static function getLists(){
      $scheds = new ScheduleController();
      $lists = $scheds->lists(['ID' => self::getId(),'SY' => self::getSy(),'SEM'=>self::getSem()]);
      return $lists;
  }

  public static function ListsToCMB($syear,$sect,$size = 'form-select-sm'){
      $lists = self::getLists();
      if (count($lists) <= 0)
        return "";

      $out = "<select class = '".$size." form-select enrolchk' id = 'schedules-".self::getId()."' name = 'schedules-".self::getId()."' >";
      $out .= '<option value = "'.Crypt::encryptstring(0).'">SELECT FROM THE AVAILABLE SCHEDULES</option>';
      foreach($lists as $list){
        $sel = '';
        if ($list->student_year == $syear and strtolower($list->section) == strtolower($sect)){
          $sel = 'selected';
        }
        $out .= '<option '.$sel.' value = "'.Crypt::encryptstring($list->id).'">'.$list->coursecode.' ('.$list->Time1.(empty($list->Time2)?"":" and ".$list->Time2).') #'.$list->cEnrolled.'</option>';
      }
      $out .= '</select>';
      return $out;
  }


  /**
   * Get the value of id
   */
  public static function getId()
  {
    return self::$id;
  }

  /**
   * Set the value of id
   *
   * @return  self
   */
  public static function setId($id)
  {
    self::$id = $id;
    return self::$id;
  }

  /**
   * Get the value of sy
   */
  public static function getSy()
  {
    return self::$sy;
  }

  /**
   * Set the value of sy
   *
   * @return  self
   */
  public static function setSy($sy)
  {
    self::$sy = $sy;
    return self::$sy;
  }

  /**
   * Get the value of sem
   */
  public static function getSem()
  {
    return self::$sem;
  }

  /**
   * Set the value of sem
   *
   * @return  self
   */
  public static function setSem($sem)
  {
    self::$sem = $sem;
    return self::$sem;
  }
}
?>
