<?php namespace outputting\error;

use \classes\BaseStandardData;

class Standard extends BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type='error';
  
  //Return true for Exception. False otherwise.
  public static function accepts($input)
  {
    
    return ($input instanceof \Exception);
    
  }
  
}
