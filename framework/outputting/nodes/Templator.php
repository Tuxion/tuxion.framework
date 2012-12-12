<?php namespace outputting\nodes;

use \classes\BaseTemplator as Base;
use \classes\BaseStandardData;

class Templator extends Base
{
  
  //Forward to nodes.
  public function __get($key)
  {
    
    $raw = $this->data->raw();
    
    if(!array_key_exists($key, $raw)){
      return new \classes\data\Undefined;
    }
    
    if(is_array($raw[$key])){
      return new \classes\Templator($raw[$key]);
    }
    
    if(is_object($raw[$key])){
      return $raw[$key];
    }
    
    return wrap($raw[$key]);
    
  }
  
}
