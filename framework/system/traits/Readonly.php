<?php namespace traits;

trait Readonly
{

  //Try to return a read-only property.
  public function __get($key)
  {
    
    if(!property_exists($this, "_$key")){
      throw new \exception\Programmer('Property %s does not exist.', $key);
    }
    
    return $this->{"_$key"};
    
  }
  
  //Warn the programmer when setting read-only properties.
  public function __set($key, $value)
  {
    
    if(!property_exists($this, "_$key")){
      throw new \exception\Programmer('Property %s does not exist.', $key);
    }
    
    throw new \exception\Programmer('Property %s is read-only.', $key);
    
  }

}
