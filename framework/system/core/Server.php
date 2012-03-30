<?php namespace core;

class Server
{
  
  private $data;
  
  public function __construct()
  {
    
    $this->data = $_SERVER;
    unset($_SERVER);
    
  }
  
  public function __get($key)
  {
    
    $k = strtoupper($key);
    
    if(!array_key_exists($k, $this->data)){
      throw new \exception\NotFound('Server variable "%s" does not exist.', $key);
    }
    
    return $this[$k];
    
  }
  
}