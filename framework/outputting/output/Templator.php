<?php namespace outputting\output;

use \classes\BaseTemplator as Base;
use \classes\BaseStandardData;

class Templator extends Base
{
  
  //Echo the raw data.
  public function __toString()
  {
    
    return $this->data->raw()->data;
    
  }
  
}
