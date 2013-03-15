<?php namespace outputting\error;

use \classes\BaseTemplator as Base;
use \classes\Materials;

class Templator extends Base
{
  
  //Public properties.
  public
    $exception,
    $debug,
    $type;
  
  //Set some data.
  public function __construct(Standard $data, Materials $materials)
  {
    
    $this->exception = $data->raw();
    $this->type = wrap($this->exception)->baseclass()->get();
    $this->debug = tx('Config')->config->debug;
    
    parent::__construct($data, $materials);
    
  }
  
}
