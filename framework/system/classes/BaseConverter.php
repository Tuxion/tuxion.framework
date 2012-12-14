<?php namespace classes;

abstract class BaseConverter
{
  
  //Protected properties.
  protected
    $standard=null,
    $raw=null;
  
  //Construct with initial data.
  final public function __construct($data)
  {
    
    if($data instanceof BaseStandardData){
      $this->standard = $data;
    }
    
    elseif($data instanceof OutputData){
      $this->raw = $data;
    }
    
    else{
      throw new \exception\InvalidArgument(
        'A converter accepts either standardized data in the form of a StandardData '.
        'object or raw data in the form of an OuputData object. %s given.',
        typeof($data)
      );
    }
    
  }
  
  //Should return or echo the raw output.
  abstract protected function convertToRaw();
  
  //Should return the data required for the standard data.
  abstract protected function convertToStandard();
  
  //Caches and returns $this->raw converted to StandardData.
  public function standardize()
  {
    
    //Check cache.
    if(!is_null($this->standard)){
      return $this->standard;
    }
    
    //Get the type.
    $type = explode('\\', get_class($this))[1];
    
    //Create the class.
    $class = "\\outputting\\$type\\Standard";
    
    //Get the data.
    $data = $this->convertToStandard();
    
    //Validate the data.
    if(!$class::accepts($data)){
      throw new \exception\BadImplementation(
        'The return value of the convertToStandard method implemented in %s does not '.
        'return data that is suitable for %s. %s given.',
        get_class($this), $type, typeof($data)
      );
    }
    
    //Convert the raw data and create the object.
    $this->standard = new $class($data);
    
    //Return the object.
    return $this->standard;
    
  }
  
  //Get or create OutputData.
  public function output()
  {
    
    return (is_null($this->raw) ? new OutputData($this->convertToRaw(false)) : $this->raw);
    
  }
  
}
