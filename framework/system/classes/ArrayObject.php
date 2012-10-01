<?php namespace classes;

class ArrayObject implements \IteratorAggregate, \ArrayAccess
{
  
  use \traits\ArrayContainer
  ;#TEMP: causes memory corruption
  //{
  //   set as private _set;
  // }
  
  //The constructor accepts the initial array.
  public function __construct($arr=[])
  {
    $this->set($arr);
  }
  
  //Magic get.
  public function __get($key)
  {
    return $this->arrayGet($key);
  }
  
  //Magic set.
  public function __set($key, $value)
  {
    return $this->arraySet($key, $value);
  }
  
  //Magic unset.
  public function __unset($key)
  {
    return $this->arrayUnset($key);
  }
  
  //Magic isset.
  public function __isset($key)
  {
    return $this->offsetExists($key);
  }
  
  #TEMP: causes memory corruption.
  // //Extend the ArrayContainer
  // public function set($arr)
  // {
    
  //   if($arr instanceof self){
  //     $arr = $arr->arr;
  //   }
    
  //   if(!is_array($arr)){
  //     throw new \exception\InvalidArgument('Expecting an array or ArrayObject. %s given.', typeof());
  //   }
    
  //   $this->_set($arr);
    
  // }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetGet($key)
  {
    return $this->arrayGet($key);
  }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetSet($key, $val)
  {
    return $this->arraySet($key, $val);
  }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetExists($key)
  {
    return $this->arrayExists($key);
  }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetUnset($key)
  {
    return $this->arrayUnset($key);
  }
  
  //Semi-magic method implemented by \IteratorAggregate.
  public function getIterator()
  {
    return new \ArrayIterator($this->arr);
  }
  
}
