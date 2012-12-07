<?php namespace classes;

class ArrayObject implements \IteratorAggregate, \ArrayAccess
{
  
  use \traits\ArrayContainer
  ;#TEMP: causes memory corruption
  //{
  //   set as private _set;
  // }
  
  
  ##
  ## MAGIC METHODS
  ##
  
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
  
  
  ##
  ## TRANSFORMATION METHODS
  ##
  
  //Return a new ArrayObject with the keys of this array as values.
  public function keys()
  {
    
    return new self(array_keys($this->arr));
    
  }
  
  //Return a new ArrayObject with the values of this array as values.
  public function values()
  {
    
    return new self(array_values($this->arr));
    
  }
  
  //Return a new ArrayObject by iterating over the data and using the return value from the callback and return it.
  public function map(\Closure $callback)
  {
    
    //Create the new instance and set the counter to zero.
    $return = new self;
    $i = 0;
    
    //Call the given callback for every node, passing its value, key and the counter.
    foreach($this->arr as $key => $value){
      $return->push($callback($value, $key, $i));
      $i++;
    }
    
    //Return the new instance, filled with the return values of the called callbacks.
    return $return;
  
  }
  
  //Return a new ArrayObject filled with the nodes that were at the given key.
  public function pluck()
  {
    
    //Create a new instance.
    $return = new self;
    
    //For every node in our current array.
    foreach($this->arr as $node)
    {
      
      //Grab the sub node of the current node for every argument passed to this function.
      foreach(func_get_args() as $key){
        $node = $node->arrayGet($key);
      }
      
      //Push the last node into the new instance.
      $return->push($node);
      
    }
    
    //Return the new instance.
    return $return;
    
  }
  
  //Return a new ArrayObject, excluding the nodes that were not in the given keys.
  public function having(array $keys)
  {
    
    //Create a new instance.
    $return = new self;
    
    //For every given key.
    foreach($keys as $key1 => $key2)
    {
      
      //If a new key name has been given.
      if(is_string($key1)){
        $return->arraySet($key1, $this->arrayGet($key2));
      }
      
      //Use the same key name.
      else{
        $return->arraySet($key2, $this->arrayGet($key2));
      }
      
    }
    
    //Return the new instance.
    return $return;
    
  }
  
  //Returns a new ArrayObject, excluding the nodes that were in the given keys.
  public function without(array $keys)
  {
    
    //Return a new instance with the computed difference in keys between the current array and given keys.
    return new self(array_diff($this->arr, array_fill_keys($keys, null)));
    
  }
  
  //Return a new ArrayObject containing only the nodes that made the given callback return true.
  public function filter(\Closure $callback)
  {
    
    //Create the new instance.
    $return = new self;
    
    //For every node, call the callback and add the node to the instance of the callback returned true.
    foreach($this->arr as $k => $v){
      if($callback($v, $k) === true){
        $return->arraySet($k, $v);
      }
    }
    
    //Return the new instance.
    return $return;
    
  }
  
  //Returns a slice of the array in the form of a new ArrayObject.
  public function slice($offset=0, $length=null)
  {
    
    //Return a new instance with a slice of the current array.
    return new self(array_slice($this->arr, $offset, $length));
    
  }
  
  //Flatten the array and return a flat new ArrayObject.
  public function flatten($blank=false)
  {
    
    return new self(array_flatten($this->toArray()));
    
  }
  
  
  ##
  ## ARRAY METHODS
  ##
  
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
  
}
