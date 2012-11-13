<?php namespace outputting\error;

use \classes\BaseTemplator as Base;
use \classes\BaseStandardData;

class Templator extends Base
{
  
  //Public properties.
  public
    $exception,
    $debug,
    $type;
  
  //Set some data.
  public function __construct(Standard $data, \classes\Materials $materials)
  {
    
    $this->exception = $data->raw();
    $this->type = baseclass(get_class($this->exception));
    $this->debug = tx('Config')->config->debug;
    
    parent::__construct($data, $materials);
    
  }
  
}
