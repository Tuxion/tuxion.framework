<?php namespace traits;

trait Readonly
{
  
  //Magic read.
  // public function __get($key)
  // {
    
  //   return $this->_read($key);
    
  // }
  
  //Warn the programmer when setting read-only properties.
  public function __set($key, $value)
  {
    
    if(!property_exists($this, "_$key")){
      throw new \exception\Programmer('Property %s does not exist.', $key);
    }
    
    throw new \exception\Programmer('Property %s is read-only.', $key);
    
  }
  
  //Try to return a read-only property.
  private function _read($key)
  {
    
    if(!property_exists($this, "_$key")){
      throw new \exception\Programmer('Property %s does not exist.', $key);
    }
    
    return $this->{"_$key"};
    
  }

}
