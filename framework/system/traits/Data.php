<?php namespace traits;

trait Data
{
  
  //Use the successable trait, and implement its is() and not() method as private, so we can extend them.
  use \traits\Successable {
    is as private _is;
    not as private _not;
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
  
  //Returns true if this node has childnodes (is not a leafnode).
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
  //back, where 1 is the direct parent.
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
