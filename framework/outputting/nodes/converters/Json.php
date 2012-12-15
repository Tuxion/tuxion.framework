<?php namespace outputting\nodes\converters;

use \classes\BaseConverter;
use \classes\data\ArrayWrapper;

class Json extends BaseConverter
{
  
  //Converts the standard data to JSON.
  protected function convertToRaw()
  {
    
    return $this->standard->raw()->toJSON()->get();
    
  }
  
  //Converts raw data to standard data.
  protected function convertToStandard()
  {
    
    return new ArrayWrapper(json_decode($this->raw->data));
    
  }
  
}
