<?php namespace outputting\nodes;

use \classes\BaseStandardData;
use \classes\data\ArrayWrapper;

class Standard extends BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type='nodes';
  
  //Return true for arrays. False otherwise.
  public static function accepts($input)
  {
    
    return ($input instanceof ArrayWrapper);
    
  }
  
}
