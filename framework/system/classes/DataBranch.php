<?php namespace classes;

class DataBranch extends ArrayObject
{
  
  //Use the shared Data trait.
  //Use the successable trait, and implement its is() and not() method as private, so we can extend them.
  public $success=null;
  
  //Sets the success state to the boolean that is given, or returned by given callback.
  public function _is($check)
  {
    
    $this->success = $this->_doCheck($check);
    return $this;
    
  }
  
  //Sets the success state to the opposite of what $this->is() would set it to with the same arguments.
  public function _not($check)
  {
    
    $this->success = !$this->_doCheck($check);
    return $this;
    
  }
  
  //Combines the current success state with what the new success state would be if is() would be called with the given arguments.
  public function andIs($check)
  {
    
    if($this->success === false){
      return $this;
    }
    
    return $this->is($check);
    
  }
  
  //Combines the current success state with what the new success state would be if not() would be called with the given arguments.
  public function andNot($check)
  {
    
    if($this->success === false){
      return $this;
    }
    
    return $this->not($check);
    
  }
  
  //Returns true, or executes $callback($this) if $this->success is true.
  public function success(callable $callback)
  {
  
    if($this->success === true){
      $return = $callback($this);
      if(!is_null($return)) return $return;
    }
      
    return $this;
    
  }
  
  //Returns true, or executes $callback($this) if $this->success is false.
  public function failure(callable $callback=null)
  {
    
    if($this->success === false){
      $return = $callback($this);
      if(!is_null($return)) return $return;
    }
    
    return $this;
    
  }
  
  //Convert given $check to boolean.
  private function _doCheck($check)
  {
    
    if($check instanceof \Closure){
      return (bool) $check($this);
    }
    
    elseif(uses($check, 'Successable')){
      return $check->success === true;
    }
    
    else{
      return (bool) $check;
    }
    
  }  
  //Properties.
  private
    $key=false,
    $parent=false;
  
  //Return a clone.
  public function copy()
  {
    
    return clone $this;
    
  }
  
  //Returns true if the value of this node is equal to true.
  public function isTrue()
  {
    return ($this->isLeafnode() && $this->get() === true);
  }
  
  //Returns true if the value of this node is equal to false.
  public function isFalse()
  {
    return ($this->isLeafnode() && $this->get() === false);
  }
  
  //Returns true if this node is set, and false if it's node.
  public function isDefined()
  {
    return !($this instanceof \classes\DataUndefined);
  }
  
  //Returns true if this node has no children.
  public function isLeafnode()
  {
    return !($this instanceof \classes\DataBranch);
  }
  
  //Returns true if this node has a parent node.
  public function isChildnode()
  {
    return ($this->parent !== false);
  }
  
  //Returns true if this node has child nodes (is not a leaf-node).
  public function isParent()
  {
    return ($this instanceof \classes\DataBranch);
  }
  
  //Returns true if this node has no parents.
  public function isRootnode()
  {
    return ($this->parent === false);
  }
  
  //Returns true if the value of this node is numeric.
  public function isNumeric()
  {
    return is_numeric($this->get());
  }
  
  //Extend the Successable trait is() function.
  public function is($check)
  {
    
    
    if(is_string($check))
    {
      
      $check = ucfirst($check);
      
      if(!method_exists($this, "is$check")){
        throw new \exception\InvalidArgument('"%s" is not a valid check.', $check);
      }
      
      $r = $this->_is($this->{"is$check"}());
      
    }
    
    else{
      $r = $this->_is($check);
    }
    
    return Data($r);
    
  }
  
  //Extend the Successable trait not() function.
  public function not($check)
  {
  
    if(is_string($check))
    {
      
      $check = ucfirst($check);
      
      if(!method_exists($this, "is$check")){
        throw new \exception\InvalidArgument('"%s" is not a valid check.', $check);
      }
      
      $r = $this->_not($this->{"is$check"}());
      
    }
    
    else{
      $r = $this->_not($check);
    }
    
    return Data($r);
  
  }
  
  //Return the direct parent, or if an integer is provided as first argument, the ancestor that many levels
  //back, where 1 is the direct parent (yes, $this->parent(0) == $this).
  public function parent($ancestor=1)
  {
    
    $return = $this;
    
    for($i=0; $i < $ancestor; $i++)
    {
      
      $return = $return->parent;
      
      if($return === false){
        break;
      }
      
    }
    
    return $return;
    
  }
  
  //Return the key of this data node.
  public function key()
  {
    
    return $this->key;
    
  }
  
  //Used internally to handle context of Data nodes.
  private function _setContext($key=false, $parent=false)
  {
    
    if(!is_null($key)){
      $this->key = $key;
    }
    
    if($parent === false){
      $this->parent = false;
    }
    
    elseif(!is_null($parent))
    {
    
      if(!($parent instanceof \classes\DataBranch)){
        throw new \exception\InvalidArgument('Expecting $parent to be an instance of DataBranch. %s given.', ucfirst(typeof($parent)));
      }
      
      $this->parent = $parent;
    
    }
    
    return $this;
    
  }

  
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
      $this->arrayGet($key)->_setContext($this, $key);
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
          $array[$key] = $normalizer($array);
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
