<?php namespace outputting\nodes;

use \classes\BaseTemplator;
use \classes\BaseStandardData;
use \classes\data\Undefined;
use \classes\Templator as CTemplator;

class Templator extends BaseTemplator
{
  
  //Forward to nodes.
  public function __get($key)
  {
    
    $raw = $this->data->raw();
    
    if(!array_key_exists($key, $raw)){
      return new Undefined;
    }
    
    if(is_array($raw[$key])){
      return new CTemplator($raw[$key]);
    }
    
    if(is_object($raw[$key])){
      return $raw[$key];
    }
    
    return wrap($raw[$key]);
    
  }
  
}
