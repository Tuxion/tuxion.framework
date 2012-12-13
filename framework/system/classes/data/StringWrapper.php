<?php namespace classes\data;

class StringWrapper extends BaseScalarData
{
  
  const TRIM_DEFAULTS = ' \t\n\r\0\x0B';
  
  //Validate and set the value.
  public function __construct($value)
  {
    
    raw($value);
    
    if(!is_string($value)){
      throw new \exception\InvalidArgument('Expecting $value to be string. %s given.', typeof($value));
    }
    
    $this->value = $value;
    
  }
  
  //Return $this. ;)
  public function toString()
  {
    
    return $this;
    
  }
  
  //Return a NumberWrapper with the integer value of the string.
  public function toInt()
  {
    
    return new NumberWrapper(intval($this->value));
    
  }
  
  //Set the value of this leaf-node.
  public function set($value)
  {
    
    //We must be scalar!
    if(is_array($value) || $value instanceof DataBranch){
      throw new \exception\InvalidArgument(
        'Expecting $value to be scalar. %s given.',
        ucfirst(typeof($value))
      );
    }
    
    //Extract data into this new leaf.
    if($value instanceof self){
      $value = $value->get();
    }
    
    //Set the value.
    $this->value = $value;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Returns the wrapped alternative if the current value is empty.
  public function alt($alternative)
  {
    
    return ($this->isEmpty() ? wrap($alternative) : $this);
    
  }
  
  //Trim specified characters off the start end end of the string.
  //trim([$direction = BOTH, ][$characters = self::TRIM_DEFAULTS]);
  public function trim()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Get direction.
    $direction = ((!empty($args) && is_int($args[0])) ? array_shift($args) : BOTH);
    
    //Get characters.
    $characters = ((!empty($args)) ? array_shift($args) : self::TRIM_DEFAULTS);
    
    //Do the right trim.
    switch($direction){
      case LEFT: return new self(ltrim($characters));
      case BOTH: return new self(trim($characters));
      case RIGHT: return new self(rtrim($characters));
    }
    
  }
  
  //Alias of trim(RIGHT).
  public function chop($characters = self::TRIM_DEFAULTS)
  {
    
    return $this->trim(RIGHT, $characters);
    
  }
  
  //Pad the string to a certain length with another string.
  //pad([$direction = RIGHT, ]$length[, $padding = ' ']);
  public function pad()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Get direction.
    $direction = ((count($args) > 1 && is_int($args[1])) ? array_shift($args) : RIGHT);
    
    //Get length.
    $length = array_shift($args);
    
    //Get padding.
    $padding = ((!empty($args)) ? array_shift($args) : ' ');
    
    //Define types.
    $types = [LEFT => STR_PAD_LEFT, BOTH => STR_PAD_BOTH, RIGHT => STR_PAD_RIGHT];
    
    //Go!
    return new self(str_pad($this->value, $length, $padding, $types[$direction]));
    
  }
  
  //Cut off the string if it's longer than [max], then [append] something to it.
  public function max($max, $append = '')
  {
    
    //Convert input to integer.
    $max = (int) $max;
    
    //Cut it up?
    if(strlen($this->value) > $max + strlen($append)){
      return new self(substr($this->value, 0, $max).$append);
    }
    
    //Do nothing.
    return new self($this->value);
    
  }
  
  //Repeat the string n times.
  public function repeat($n)
  {
    
    return new self(str_repeat($this->value, $n));
    
  }
  
  //Replaces [search] with [replacement] and fills [count] with the amount of replacements done.
  public function replace($search, $replacement='', &$count = 0)
  {
    
    return new self(str_replace($search, $replacement, $this->value, $count));
    
  }
  
  //Return a slice of the string.
  public function slice($offset=0, $length=null)
  {
    
    return new self(substr($this->value, $offset, $length));
    
  }
  
  //Perform a regular expression and return a wrapped array containing the matches.
  public function parse($regex, $flags=0)
  {
    
    //Try to parse using the given arguments.
    try{
      preg_match($regex, $this->get(), $matches, $flags);
      return new ArrayWrapper($matches);
    }
    
    //Throw a parsing exception when it fails.
    catch(\exception\Error $e){
      $new = new \exception\Parsing(
        'An error occured while parsing "%s" using "%s": %s',
        $this->value, $regex, $e->getMessage()
      );
      $new->setPrev($e);
      throw $new;
    }
    
  }
  
  //Return a new DataLeaf, containing the value of this one but lowercased.
  public function lowercase()
  {
    
    return new self(strtolower($this->value));
    
  }
  
  //Return a new DataLeaf, containing the value of this one but uppercased.
  public function uppercase()
  {
    
    return new self(strtoupper($this->value));
    
  }
  
  //Return a new DataLeaf containing the HTML escaped value of this node.
  public function htmlescape($flags=50)
  {
    
    return new self(htmlentities($this->value, $flags, 'UTF-8'));
    
  }
  
  //Split the string value of this node into pieces, give string to use it as delimiter
  //or int to split into chunks of given size.
  public function split($s=null)
  {
    
    //Return an empty array if the string is empty.
    if($this->isEmpty()){
      return new ArrayWrapper([]);
    }
    
    //Split the string into characters.
    if(empty($s) || (is_int($s) && $s < 1)){
      $split = str_split($this->value);
    }
    
    //Split the string into chunks.
    elseif(is_int($s)){
      $split = str_split($this->value, $s);
    }
    
    //Split the string on the given character.
    elseif(is_string($s)){
      $split = explode($s, $this->value);
    }
    
    return new ArrayWrapper($split);
    
  }
  
  //Return the length of the string contained in this node.
  public function length()
  {
    
    return strlen($this->value);
    
  }
  
  //Returns true of this string has no characters.
  public function isEmpty()
  {
    
    return empty($this->value);
    
  }
  
}
