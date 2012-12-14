<?php namespace outputting\output;

use \classes\BaseStandardData;
use \classes\OutputData;

class Standard extends BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type='output';
  
  //Return true for OutputData. False otherwise.
  public static function accepts($input)
  {
    
    return ($input instanceof OutputData);
    
  }
  
}
