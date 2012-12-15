<?php namespace outputting\nodes;

use \classes\BaseTemplator;

class Templator extends BaseTemplator
{
  
  //Forward to nodes.
  public function __get($key)
  {
    
    return $this->data->raw()->wrap($key);
    
  }
  
}
