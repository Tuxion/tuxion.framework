<?php namespace outputting\nodes;

class Standard extends \classes\BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type='nodes';
  
  //Return true for arrays. False otherwise.
  public static function accepts($input)
  {
    
    return ($input instanceof \classes\ArrayObject);
    
  }
  
}
