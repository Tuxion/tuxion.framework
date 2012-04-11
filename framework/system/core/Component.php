<?php namespace core;

class Component implements \ArrayAccess
{

  public function __get($key)
  {
    return \classes\Component::get($key);
  }

  //Simi-magic method implemented by \ArrayAccess.
  public function offsetGet($key)
  {
    return \classes\Component::get($key);
  }
  
  //Simi-magic method implemented by \ArrayAccess.
  public function offsetSet($key, $val)
  {
    throw new \exception\Restriction('Components are read-only.');
  }
  
  //Simi-magic method implemented by \ArrayAccess.
  public function offsetExists($key)
  {
    
    try{
      \classes\Component::get($key);
      return true;
    }
    
    catch(\exception\NotFound $e){
      return false;
    }
    
  }
  
  //Simi-magic method implemented by \ArrayAccess.
  public function offsetUnset($key)
  {
    throw new \exception\Restriction('Components are read-only.');
  }

}
