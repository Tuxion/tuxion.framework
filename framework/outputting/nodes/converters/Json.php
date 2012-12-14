<?php namespace outputting\nodes\converters;

use \classes\BaseConverter;

class Json extends BaseConverter
{
  
  //Converts the standard data to JSON.
  protected function convertToRaw()
  {
    
    return json_encode($this->standard->raw());
    
  }
  
  //Converts raw data to standard data.
  protected function convertToStandard()
  {
    
    return json_decode($this->raw->data);
    
  }
  
}
