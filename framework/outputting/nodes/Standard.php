<?php namespace outputting\nodes;

use \classes\BaseStandardData;

class Standard extends BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type='nodes';
  
  //Return true for arrays. False otherwise.
  public static function accepts($input)
  {
    
    return is_array($input);
    
  }
  
}
