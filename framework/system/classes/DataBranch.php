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
      throw new \exception\InvalidArgument(
        'Expecting $data to be array or to use ArrayContainer. %s given.',
        ucfirst(typeof($data))
      );
    }
    
    $this->_setContext($key, $parent);
    parent::__construct($data);
    
  }
  
  //Clone our children when we get ourselves cloned.
  public function __clone()
  {
    
    $this->each(function($val, $key){
      $this->arraySet($key, clone $val);
      $this->arrayGet($key)->_setContext($key, $this);
    });
    
  }
  
  //Extend join()
  public function join($separator='')
  {
    
    return new \classes\DataLeaf(parent::join($separator));
    
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
  
  //Return true if the sub-node under the given key has a value of true, or true-ish when
  //$strict is set to false.
  public function check($key, $strict=true)
  {
    
    if($strict === false){
      return ($this->offsetExists($key)
        && ($this->arrayGet($key) instanceof DataBranch)
        || ($this->arrayGet($key) instanceof DataLeaf
          && $this->arrayGet($key)->get() == true
        )
      );
    }
    
    return ($this->offsetExists($key)
      && ($this->arrayGet($key) instanceof DataLeaf
        && $this->arrayGet($key)->get() === true
      )
    );
    
  }
  
  //Returns "array".
  public function type()
  {
    
    return 'array';
    
  }
  
  //Alias for toArray(false).
  public function get()
  {
    
    return $this->toArray(false);
    
  }
  
  //Extend the toArray method to take DataLeafs and DataUndefined nodes into account.
  public function toArray($recursive=true)
  {
    
    $array = parent::toArray($recursive);
    
    if($recursive === false){
      return $array;
    }
    
    $normalizer = function($array)use(&$normalizer)
    {
      
      foreach($array as $key => $value)
      {
        
        if($value instanceof DataLeaf){
          $array[$key] = $value->get();
        }
        
        elseif($value instanceof DataUndefined){
          unset($array[$key]);
        }
        
        elseif(is_array($value)){
          $array[$key] = $normalizer($value);
        }
        
      }
      
      return $array;
      
    };
    
    return $normalizer($array);
    
  }
  
  //Extend the arrayGet of ArrayObject to allow new DataUndefined nodes to be created if
  //non-existing nodes are requested.
  public function arrayGet($key)
  {
    
    //If the requested node does not exist, we will create an Undefined node.
    if(!$this->offsetExists($key)){
      return $this->arraySet($key, new \classes\DataUndefined($this, $key));
    }
    
    return parent::arrayGet($key);
    
  }
  
  //Extend the arraySet of ArrayObject to ensure that only DataBranches or DataLeafs are used.
  public function arraySet($key, $value)
  {
    
    //Numeric keys will be cast to integers, and increase the I of this DataBranch.
    if(is_numeric($key))
    {
      
      $key = (int) $key;
      
      if($key > $this->i){
        $this->i = $key;
      }
      
    }
    
    //Null or empty keys will trigger auto-increment.
    elseif(is_null($key) || empty($key)){
      $key = $this->i;
    }
    
    //Array values will make new DataBranches.
    if(is_array($value) || uses($value, 'ArrayContainer')){
      $value = new $this($value, $this, $key);
    }
    
    //DataUndefined will be cloned and used.
    elseif($value instanceof DataUndefined){
      $value = clone $value;
    }
    
    //Scalar values will make new DataLeafs.
    else{
      $value = new DataLeaf($value, $this, $key);
    }
    
    //If this will create a node, increase the i.
    if(!$this->offsetExists($key)){
      $this->i++;
    }
    
    return parent::arraySet($key, $value);
    
  }
  
}
