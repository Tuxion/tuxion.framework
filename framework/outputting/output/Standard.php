<?php namespace outputting\output;

class Standard extends \classes\BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type='output';
  
  //Return true for OutputData. False otherwise.
  public static function accepts($input)
  {
    
    return ($input instanceof \classes\OutputData);
    
  }
  
}
