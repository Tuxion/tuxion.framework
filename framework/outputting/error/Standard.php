<?php namespace outputting\error;

class Standard extends \classes\BaseStandardData
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
