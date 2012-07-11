<?php namespace classes;

class DataLeaf
{
  
  //Use the shared Data trait.
  use \traits\Data;
  
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
    
    if(is_array($value) || $value instanceof DataBranch){
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
