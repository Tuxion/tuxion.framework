<?php namespace core;

class Component implements \ArrayAccess
{
  
  //Initiate.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'Component class initializing.');
    tx('Log')->message(__CLASS__, 'Component class initialized.');
    
  }
  
  //Return a component object.
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
