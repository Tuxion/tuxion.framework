<?php namespace classes;

class DataLeaf
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
  private $value = null;
  
  //Constructor can accept initial data.
  public function __construct($value=null, $parent=null, $key=null)
  {
    
    $this->_setContext($key, $parent);
    $this->set($value);
    
  }
  
  //Return the string value of this node when it is cast to strong somehow.
  public function __toString()
  {
    
    return $this->get('string');
    
  }
  
  //Get the raw value from this DataLeaf.
  public function get($as=null)
  {
    
    if(is_null($as)){
      return $this->value;
    }
    
    switch($as)
    {
      
      case 'int':
      case 'integer':
        return (int) $this->value;
        break;
        
      case 'real':
      case 'float':
      case 'double':
        return (double) $this->value;
        break;
        
      case 'str':
      case 'string':
        return (string) $this->value;
        break;
        
      case 'bool':
      case 'boolean':
        return (bool) $this->value;
        break;
        
    }
    
  }
  
  //Set the value of this leaf-node.
  public function set($value)
  {
    
    if(is_array($value)){
      throw new \exception\InvalidArgument('Expecting $value to be scalar. %s given.', ucfirst(typeof($value)));
    }
    
    $this->value = $value;
    
  }
  
  //Trim specified characters off the start end end of the node.
  public function trim($charlist=' ')
  {
    
    return new $this(trim($this->get('str'), $charlist));
    
  }
  
  //Return a slice of the string.
  public function slice($offset=0, $length=null)
  {
    
    return new $this(substr($this->get('str'), $offset, $length));
    
  }
  
  //Perform a regular expression and return a new DataBranch containing the matches.
  public function parse($regex, $flags=0)
  {
    
    if(!is_string($regex)){
      throw new \exception\InvalidArgument('Expecting $regex to be string. %s given.', ucfirst(gettype($regex)));
    }
    
    try{
      preg_match($regex, $this->get(), $matches, $flags);
      return new \classes\DataBranch($matches);
    }
    
    catch(\exception\Error $e){
      throw new \exception\Programmer('An error occured while parsing "%s" using "%s": %s', $this->get('str'), $regex, $e->getMessage());
    }
    
  }
  
  //Return a new DataLeaf, containing the value of this one but lowercased.
  public function lowercase()
  {
    
    return new $this(strtolower($this->get('str')));
    
  }
  
  //Return a new DataLeaf, containing the value of this one but uppercased.
  public function uppercase()
  {
    
    return new $this(strtoupper($this->get('str')));
    
  }
  
  //Return a new DataLeaf containing the HTML escaped value of this node.
  public function htmlescape($flags=50)
  {
    
    return new $this(htmlentities($this->get('str'), $flags, 'UTF-8'));
    
  }
  
  //Split the string value of this node into pieces, give string to use it as delimiter or int to split into chunks of given size.
  public function split($s=null)
  {
    
    $return = new $this;
    
    if($this->is_empty()){
      return $return;
    }
    
    if(empty($s) || (is_int($s) && $s < 1)){
      $split = str_split($this->get('str'));
    }
    
    elseif(is_int($s)){
      $split = str_split($this->get('str'), $s);
    }
    
    elseif(is_string($s)){
      $split = explode($s, $this->get('str'));
    }
    
    return $return->set($split);
    
  }
  
  //Return the length of the string contained in this node.
  public function length()
  {
    
    return strlen($this->get('str'));
    
  }
  
  //Return the type of the DataLeaf's value.
  public function type()
  {
    
    return gettype($this->value);
    
  }
  
  //Returns true of this node can be considered empty.
  public function isEmpty()
  {
    
    return empty($this->value);
    
  }
  
  //Uses Successable to implement greater than with short notation.
  public function gt($value, $callback=null)
  {
    
    if(!is_numeric($value)){
      throw new \exception\InvalidArgument('Expecting $value to be numeric. %s given.', ucfirst(typeof($value)));
    }
    
    return $this->is($this->value > $value, $callback);
    
  }
  
  //Uses Successable to implement less than with short notation.
  public function lt($value, $callback=null)
  {
    
    if(!is_numeric($value)){
      throw new \exception\InvalidArgument('Expecting $value to be numeric. %s given.', ucfirst(typeof($value)));
    }
    
    return $this->is($this->value < $value, $callback);
    
  }
  
  //Uses Successable to implement equals with short notation.
  public function eq($value, $callback=null)
  {
  
    return $this->is($this->value == $value, $callback);
    
  }
  

}
