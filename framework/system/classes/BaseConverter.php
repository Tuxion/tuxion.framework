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
  abstract protected function convertToRaw($to_stream=true);
  
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
  
  //Converts $this->standard to raw data, and either outputs it directly to the
  //stream, or caches and returns an instance of OutputData.
  public function output($to_stream=true)
  {
    
    //Set the headers of the already present raw data?
    if(!is_null($this->raw)){
      $headers = $this->raw->headers;
    }
    
    //Create the headers.
    else{
      $headers = [];
    }
    
    //Output directly to stream?
    if($to_stream)
    {
      
      //Set the headers.
      $this->setHeaders($headers);
      
      //Output the data.
      if(is_null($this->raw)){
        $this->convertToRaw(true);
      }
      
      else{
        echo $this->raw->data;
      }
      
      //Return void.
      return;
      
    }
    
    //Return an OutputData object.
    else
    {
      
      //Check the cache.
      if(!is_null($this->raw)){
        return $this->raw;
      }
      
      //Convert the standard data and create the object.
      $this->raw = new OutputData($this->convertToRaw(false), $headers);
      
      //Return the Output data.
      return $this->raw;
      
    }
    
  }
  
  //Output headers.
  protected function setHeaders(array $headers)
  {
    
    //Iterate the given headers and output them.
    foreach($headers as $key => $value){
      header("$key: $value");
    }
    
    //Enable chaining.
    return $this;
    
  }
  
}
