<?php namespace classes;

class ArrayObject implements \IteratorAggregate, \ArrayAccess
{
  
  use \traits\ArrayContainer;
  
  public function __construct(array $arr)
  {
    $this->arr = $arr;
  }
  
  public function __get($key)
  {
    return $this->arrayGet($key);
  }
  
  public function __set($key, $value)
  {
    return $this->arraySet($key, $value);
  }
  
  public function __unset($key)
  {
    return $this->arrayUnset($key);
  }
  
  //Simi-magic method implemented by \ArrayAccess.
  public function offsetGet($key)
  {
    return $this->arrayGet($key);
  }
  
  //Simi-magic method implemented by \ArrayAccess.
  public function offsetSet($key, $val)
  {
    return $this->arraySet($key, $val);
  }
  
  //Simi-magic method implemented by \ArrayAccess.
  public function offsetExists($key)
  {
    return array_key_exists($key, $this->data);
  }
  
  //Simi-magic method implemented by \ArrayAccess.
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