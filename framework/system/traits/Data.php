<?php namespace traits;

trait Data
{
  
  //Use the successable trait, and implement its is() and not() method as private, so we can extend them.
  public $success=null;
  
  //Sets the success state to the boolean that is given, or returned by given callback.
  private function _is($check)
  {
    
    $this->success = $this->_doCheck($check);
    return $this;
    
  }
  
  //Sets the success state to the opposite of what $this->is() would set it to with the same arguments.
  private function _not($check)
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

}
