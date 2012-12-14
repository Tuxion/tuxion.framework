<?php namespace outputting\output;

use \classes\BaseTemplator;

class Templator extends BaseTemplator
{
  
  //Echo the raw data.
  public function __toString()
  {
    
    return $this->data->raw()->data;
    
  }
  
}
