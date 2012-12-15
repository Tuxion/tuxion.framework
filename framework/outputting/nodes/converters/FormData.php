<?php namespace outputting\nodes\converters;

use \classes\BaseConverter;
use \classes\data\ArrayWrapper;

class FormData extends BaseConverter
{
  
  //Converts the standard data to form data.
  protected function convertToRaw()
  {
    
    return http_build_query($this->standard->raw()->toArray(), null, ini_get('arg_separator.output'), PHP_QUERY_RFC3986);
    
  }
  
  //Converts raw data to standard data.
  protected function convertToStandard()
  {
    
    parse_str($this->raw->data, $output);
    return new ArrayWrapper($output);
    
  }
  
}
