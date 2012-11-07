<?php namespace classes;

class OutputData
{
  
  //Public properties.
  public
    $data,
    $headers=[];
  
  //Set data and headers.
  public function __construct($data, array $headers = [])
  {
    
    if(!is_string($data)){
      throw new \exception\InvalidArgument('Expecting $data to be string. %s given.', typeof($data));
    }
    
    $this->data = $data;
    $this->setHeaders($headers);
    
  }
  
  //Adds a header.
  public function setHeader($key, $value)
  {
    
    $this->headers[$key] = $value;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Adds headers.
  public function setHeaders(array $headers)
  {
    
    //Add them one by one.
    foreach($headers as $key => $value){
      $this->setHeader($key, $value);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Output the data to the stream.
  public function output()
  {
    
    //Set the headers.
    foreach($this->headers as $header => $value){
      header("$header: $value");
    }
    
    //Output the data.
    echo $this->data;
    
  }
  
}
