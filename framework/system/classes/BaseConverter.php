<?php namespace classes;

abstract class BaseConverter
{
  
  //Private properties.
  private
    $standard=null,
    $raw=null;
  
  //Construct with initial data.
  final public function __construct($data)
  {
    
    if($data instanceof \classes\BaseStandardData){
      $this->standard = $data;
    }
    
    elseif(is_string($data)){
      $this->raw = $data;
    }
    
  }
  
  //Should cache and return $this->raw converted to StandardData.
  public function standardize();
  
  //Should convert $this->standard to raw data, and either output it directly to the
  //stream, or cache and return an instance of OutputData.
  public function output($to_stream=true);
  
}
