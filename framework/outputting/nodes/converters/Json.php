<?php namespace outputting\nodes\converters;

class Json extends \classes\BaseConverter
{
  
  //Converts the standard data to JSON.
  protected function convertToRaw($to_stream=true)
  {
    
    if($to_stream){
      echo json_encode($this->standard->raw());
    }
    
    else{
      return json_encode($this->standard->raw());
    }
    
  }
  
  //Converts raw data to standard data.
  protected function convertToStandard()
  {
    
    return json_decode($this->raw->data);
    
  }
  
}
