<?php namespace outputting\nodes\converters;

use \classes\BaseConverter;

class Text extends BaseConverter
{
  
  //Converts the standard data to form data.
  protected function convertToRaw()
  {
    
    return wrap($this->standard->raw()->toArray())->visualize()->unwrap();
    
  }
  
  //Converts raw data to standard data.
  protected function convertToStandard()
  {
    
    return false;
    
  }
  
}
