<?php namespace outputting\nodes;

use \classes\BaseTemplator as Base;
use \classes\BaseStandardData;

class Templator extends Base
{
  
  //Forward to nodes.
  public function __get($key)
  {
    
    return $this->data->raw()->arrayGet($key);
    
  }
  
}
