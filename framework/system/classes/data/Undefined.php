<?php namespace classes\data;

class Undefined extends BaseData
{
  
  //Return NULL.
  public function get()
  {
    
    return null;
    
  }
  
  //Return an appropriate indication of fail.
  public function toString()
  {
    
    return new StringWrapper('[data\\Undefined]');
    
  }
  
  //Return undefined.
  public function toJSON()
  {
    
    return new StringWrapper('undefined');
    
  }
  
  //Return "NULL".
  public function visualize()
  {
    
    return new StringWrapper("NULL");
    
  }
  
  //Can not call methods on Undefined.
  public function __call($key, $args)
  {
    
    throw new \exception\Restriction('Can not call method "%s" of Undefined.', $key);
    
  }
  
  //Can not get nodes of Undefined.
  public function __get($key)
  {
    
    throw new \exception\Restriction('Can not get "%s" of DataUndefined.', $key);
    
  }
  
  //Can not set nodes of Undefined.
  public function __set($key, $value)
  {
    
    throw new \exception\Restriction('Can not set "%s" of DataUndefined.', $key);
    
  }
  
  //Return the wrapped value.
  public function alt($value)
  {
    
    return wrap($value);
    
  }
  
  //Undefined is always empty.
  public function isEmpty()
  {
    
    return true;
    
  }
  
}
