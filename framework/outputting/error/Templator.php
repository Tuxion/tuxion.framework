<?php namespace outputting\error;

use \classes\BaseTemplator as Base;
use \classes\BaseStandardData;

class Templator extends Base
{
  
  //Public properties.
  public
    $exception;
  
  //Set some data.
  public function __construct(Standard $data)
  {
    
    $this->exception = $data->raw();
    
    parent::__construct($data);
    
  }
  
}
