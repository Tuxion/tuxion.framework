<?php namespace core;

class Server implements \ArrayAccess
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
    
    return $this->data[$k];
    
  }
  
  public function __set($key, $value)
  {
    throw new \exception\Restriction('Server variables are read-only.');
  }
  
  //simi-magic method implemented by \ArrayAccess.
  public function offsetGet($key)
  {
    return $this->__get($key);
  }
  
  //simi-magic method implemented by \ArrayAccess.
  public function offsetSet($key, $val)
  {
    throw new \exception\Restriction('Server variables are read-only.');
  }
  
  //simi-magic method implemented by \ArrayAccess.
  public function offsetExists($key)
  {
    return array_key_exists($key, $this->data);
  }
  
  //simi-magic method implemented by \ArrayAccess.
  public function offsetUnset($key)
  {
    throw new \exception\Restriction('Server variables are read-only.');
  }
  
}