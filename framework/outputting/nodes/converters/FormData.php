<?php namespace outputting\nodes\converters;

class FormData extends \classes\BaseConverter
{
  
  //Converts the standard data to form data.
  protected function convertToRaw($to_stream=true)
  {
    
    if($to_stream){
      echo http_build_query($this->standard->raw(), null, ini_get('arg_separator.output'), PHP_QUERY_RFC3986);
    }
    
    else{
      return http_build_query($this->standard->raw(), null, ini_get('arg_separator.output'), PHP_QUERY_RFC3986);
    }
    
  }
  
  //Converts raw data to standard data.
  protected function convertToStandard()
  {
    
    parse_str($this->raw->data, $output);
    return $output;
    
  }
  
}
