<?php namespace classes\data;

abstract class BaseScalarData extends BaseData
{
  
  //Protected properties.
  protected
    $value;
  
  //Constructor must validate and set the value.
  abstract public function __construct($value);
  
  //Return the value.
  public function get()
  {
    
    return $this->value;
    
  }
  
  //Returns true if the value is trueish.
  public function isTrue()
  {
    
    return !!$this->value;
    
  }
  
  //Returns true if the value is falseish.
  public function isFalse()
  {
    
    return !$this->value;
    
  }
  
  //Uses Successable to implement greater than with short notation.
  public function gt($value, $callback=null)
  {
    
    if(!is_numeric($value)){
      throw new \exception\InvalidArgument(
        'Expecting $value to be numeric. %s given.', ucfirst(typeof($value))
      );
    }
    
    return $this->is($this->value > $value, $callback);
    
  }
  
  //Uses Successable to implement less than with short notation.
  public function lt($value, $callback=null)
  {
    
    if(!is_numeric($value)){
      throw new \exception\InvalidArgument(
        'Expecting $value to be numeric. %s given.', ucfirst(typeof($value))
      );
    }
    
    return $this->is($this->value < $value, $callback);
    
  }
  
  //Uses Successable to implement equals with short notation.
  public function eq($value, $callback=null)
  {
  
    return $this->is($this->value == $value, $callback);
    
  }
  
}
