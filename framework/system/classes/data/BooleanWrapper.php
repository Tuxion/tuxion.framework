<?php namespace classes\data;

class BooleanWrapper extends BaseScalarData
{
  
  //Validate and set the value.
  public function __construct($value)
  {
    
    if(!(is_bool($value))){
      throw new \exception\InvalidArgument('Expecting $value to be a boolean. %s given.', typeof($value));
    }
    
    $this->value = $value;
    
  }
  
  //Cast the boolean to string.
  public function toString()
  {
    
    return new StringWrapper((string) $this->value);
    
  }
  
  //Return a StringWrapper containing the boolean in JSON format.
  public function toJSON()
  {
    
    return $this->visualize();
    
  }
  
  //Return a StringWrapper containing the visual representation of this boolean.
  public function visualize()
  {
    
    return new StringWrapper($this->value ? 'true' : 'false');
    
  }
  
  //Return the wrapped alternative if this boolean is false.
  public function alt($alternative)
  {
    
    return ($this->isTrue() ? $this : wrap($alternative));
    
  }
  
}
