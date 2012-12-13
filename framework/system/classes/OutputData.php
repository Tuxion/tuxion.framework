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
    
    //Wrap the headers to work with them.
    $headers = wrap($this->headers);
    
    //Handle status headers with care!
    if($headers->arrayExists('Status'))
    {
      
      //Steal the value from the array and get the server protocol.
      $status = $headers->steal('Status');
      $protocol = tx('Server')->server_protocol;
      
      //If we're using PHP CGI, we'll just use a Status header. CGI will do the rest.
      if(substr(php_sapi_name(), 0, 3) == 'cgi'){
        header("Status: $status", true);
      }
      
      //No CGI? We must do it ourselves.
      else
      {
        
        //Get the code from the status.
        $code = wrap($status)->split(' ')->wrap(0)->toInt()->get();
        
        //Set a default server protocol.
        $protocol = wrap($protocol)->alt('HTTP/1.1')->get();
        
        //Set the header.
        header("$protocol $status", true, $code);
        
      }
      
    }
    
    //Set the remaining headers.
    foreach($headers as $header => $value){
      header("$header: $value", true);
    }
    
    //Output the data.
    echo $this->data;
    
  }
  
}
