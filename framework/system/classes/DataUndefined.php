<?php namespace classes;

class DataUndefined
{
  
  //Use the shared Data trait.
  use \traits\Data;
  
  //The constructor sets the context.
  public function __construct($parent, $key)
  {
    
    $this->_setContext($key, $parent);
    
  }
  
  //Can not call methods on Undefined.
  public function __call($key, $args)
  {
    
    throw new \exception\Restriction('Can not call method "%s" of DataUndefined at key %s.', $key, $this->key());
    
  }
  
  //Can not get sub-nodes of Undefined.
  public function __get($key)
  {
    
    throw new \exception\Restriction('Can not get "%s" of DataUndefined at key %s.', $key, $this->key());
    
  }
  
  //Can not set sun-nodes of Undefined.
  public function __set($key, $value)
  {
    
    throw new \exception\Restriction('Can not set "%s" of DataUndefined at key %s.', $key, $this->key());
    
  }
  
  //Replace this DataUndefined node with a new Data node that is generated based on given $value.
  public function set($value)
  {
    
    return $this->parent->arraySet($this->key, $value);
    
  }
  
}
