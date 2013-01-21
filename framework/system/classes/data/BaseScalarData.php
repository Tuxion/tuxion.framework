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
  
  //Use successable to check for the result a hasSomething method.
  //has($methodName[, $argument[, ...]])
  public function has()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Check if at least one argument is present.
    if(count($args) < 1){
      throw new \exception\InvalidArgument('Expecting at least one argument.');
    }
    
    //Get the method name.
    $method = 'has'.ucfirst(array_shift($args));
    
    //Check if it exists.
    if(!method_exists($this, $method)){
      throw new \exception\NonExistent('Method "%s" does not exist in %s.', $method, get_class());
    }
    
    //Call the method and use it to set the success state.
    $this->is(!!call_user_func_array([$this, $method], $args));
    
    //Enable chaining.
    return $this;
    
  }
  
  //Uses Successable to implement greater than with short notation.
  public function gt($value, $callback=null)
  {
    
    raw($value);
    
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
    
    raw($value);
    
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
  
    return $this->is($this->value == unwrap($value), $callback);
    
  }
  
}
