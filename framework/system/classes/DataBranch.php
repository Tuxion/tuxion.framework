<?php namespace classes;

class DataBranch extends ArrayObject
{
  
  //Use the shared Data trait.
  use \traits\Data;
  
  //Properties.
  private $i = 0;
  
  //Constructor can accept initial data.
  public function __construct($data=array(), $parent=null, $key=null)
  {
    
    if(uses($data, 'ArrayContainer')){
      $data = $data->toArray();
    }
    
    if(!is_array($data)){
      throw new \exception\InvalidArgument('Expecting $data to be array or to use ArrayContainer. %s given.', ucfirst(typeof($data)));
    }
    
    $this->_setContext($key, $parent);
    parent::__construct($data);
    
  }
  
  //Destroy the children when the parent dies.
  public function __destruct()
  {
    
    foreach($this->arr as $key => $node){
      unset($this->arr[$key]);
    }
    
  }
  
  //Clone our children when we get ourselves cloned.
  public function __clone()
  {
    
    foreach($this->arr as $key => $val){
      $this->arraySet($key, clone $val);
      $this->arrayGet($key)->_setContext($this, $key);
    }
    
  }
  
  //Can not cast non-scalar value to string.
  public function __toString()
  {
    
    throw new \exception\Restriction('Can not cast non scalar value at key "%s" to string.', $this->key());
    
  }
  
  //Extract a sub-node based on the given argument.
  public function extract()
  {
    
    $key = (func_num_args() == 1 ? func_get_arg(0) : (func_num_args() > 1 ? func_get_args() : null));
    
    if(is_scalar($key)){
      return $this->arrayGet($key);
    }
    
    $return = $this;
    
    foreach($key as $k){
      $return = $return->arrayGet($k);
    }
    
    return $return;
      
  }
  
  //Return true if the sub-node under the given key has a value of true, or true-ish when $strict is set to false.
  public function check($key, $strict=true)
  {
    
    if($strict === false){
      return ($this->offsetExists($key) && ($this->arrayGet($key) instanceof DataBranch) || ($this->arrayGet($key) instanceof DataLeaf && $this->arrayGet($key)->get() === true));
    }
    
    return ($this->offsetExists($key) && ($this->arrayGet($key) instanceof DataLeaf && $this->arrayGet($key)->get() === true));
    
  }
  
  //Extend the arrayGet of ArrayObject to allow new DataUndefined nodes to be created if non-existing nodes are requested.
  protected function arrayGet($key)
  {
    
    //If the requested node does not exist, we will create an Undefined node.
    if(!$this->offsetExists($key)){
      $this->arraySet($key, new \classes\DataUndefined($this, $key));
    }
    
    return parent::arrayGet($key);
    
  }
  
  //Extend the arraySet of ArrayObject to ensure that only DataBranches or DataLeafs are used.
  protected function arraySet($key, $value)
  {
    
    //Numeric keys will be cast to integers, and increase the I of this DataBranch.
    if(is_numeric($key))
    {
      
      $key = (int) $key;
      
      if($key > $this->i){
        $this->i = $key;
      }
      
    }
    
    //Null keys will trigger auto-increment.
    elseif(is_null($key)){
      $key = $this->i++;0
    }
    
    //Array values will make new DataBranches.
    if(is_array($value) || uses($value, 'ArrayContainer')){
      $value = new self($value, $this, $key);
    }
    
    //Scalar values will make new DataLeafs.
    else{
      $value = new DataLeaf($value, $this, $key);
    }
    
    return parent::arraySet($key, $value);
    
  }
  
}
