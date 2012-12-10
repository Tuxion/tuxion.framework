<?php namespace classes\data;

class FunctionWrapper extends BaseScalarData
{
  
  //Validate and set the value.
  public function __construct($value)
  {
    
    raw($value);
    
    if(!($value instanceof \Closure)){
      throw new \exception\InvalidArgument('Expecting $value to be a Closure. %s given.', typeof($value));
    }
    
    $this->value = $value;
    
  }
  
  //Cast the boolean to string.
  public function toString()
  {
    
    return new StringWrapper('[data\Function]');
    
  }
  
  //Return the Function, bound to a new object.
  public function bind($object)
  {
    
    return new self($this->value->bindTo($object));
    
  }
  
  //Call the function, passing on the given arguments and return the return value.
  public function call()
  {
    
    return wrap(call_user_func_array($this->value, func_get_args()));
    
  }
  
  //Call the function, using the given array of arguments and return the return value.
  public function apply(array $args)
  {
    
    return wrap(call_user_func_array($this->value, $args));
    
  }
  
  //Returns the function wrapped by the given wrapper.
  public function wrap(\Closure $wrapper)
  {
    
    $value =& $this->value;
    
    return new self(function()use(&$value, $wrapper){
      return call_user_func_array($wrapper, array_merge([$value], func_get_args()));
    });
    
  }
  
}
