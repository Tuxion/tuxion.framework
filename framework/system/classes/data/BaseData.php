<?php namespace classes\data;

abstract class BaseData
{
  
  //Use the successable trait, and implement its is() and not() method as private, so we can extend them.
  use \traits\Successable
  {
    is as private _is;
    not as private _not;
    success as private _success;
    failure as private _failure;
  }
  
  //Must implement "get" to return the native value.
  abstract public function get();
  
  //Alias for get.
  final public function unwrap()
  {
    
    return $this->get();
    
  }
  
  //Must implement string casting.
  abstract public function toString();
  
  //Magic method for when PHP casts this object to string.
  final public function __toString()
  {
    
    return $this->toString()->get();
    
  }
  
  //Return a clone.
  public function copy()
  {
    
    return clone $this;
    
  }
  
  //Returns true if this node is set, and false if it's not.
  public function isDefined()
  {
    
    return !($this instanceof Undefined);
    
  }
  
  //Returns true if this object represents a scalar data type.
  public function isScalar()
  {
    
    return ($this instanceof BaseScalarData);
    
  }
  
  //Returns true if the value of this node is numeric.
  public function isNumeric()
  {
    
    return is_numeric($this->get());
    
  }
  
  //Return true if this is an array.
  public function isArray()
  {
    
    return ($this instanceof ArrayWrapper);
    
  }
  
  //Return true if this is a string.
  public function isString()
  {
    
    return ($this instanceof StringWrapper);
    
  }
  
  //Return true if this is a number.
  public function isNumber()
  {
    
    return ($this instanceof NumberWrapper);
    
  }
  
  //Return true if this is a boolean.
  public function isBool()
  {
    
    return ($this instanceof BoolWrapper);
    
  }
  
  //Return true if this is a function.
  public function isFunction()
  {
    
    return ($this instanceof FunctionWrapper);
    
  }
  
  //Return true if this is an object.
  public function isObject()
  {
    
    return ($this instanceof ObjectWrapper);
    
  }
  
  //Return the type of the DataLeaf's value.
  public function type()
  {
    
    return gettype($this->value);
    
  }
  
  //Extend the Successable trait is() function.
  public function is($check)
  {
    
    //No string given? Do the old stuff.
    if(!is_string($check)){
      return wrap($this->_is($check));
    }
    
    //Uppercase the first letter of the given check.
    $check = ucfirst($check);
    
    //Check the existence of the check.
    if(!method_exists($this, "is$check")){
      throw new \exception\InvalidArgument('"%s" is not a valid check.', $check);
    }
    
    //Do the old stuff using the boolean returned by the given check method.
    return wrap($this->_is($this->{"is$check"}()));
    
  }
  
  //Extend the Successable trait not() function.
  public function not($check)
  {
    
    //No string given? Do the old stuff.
    if(!is_string($check)){
      return wrap($this->_not($check));
    }
    
    //Uppercase the first letter of the given check.
    $check = ucfirst($check);
    
    //Check the existence of the check.
    if(!method_exists($this, "is$check")){
      throw new \exception\InvalidArgument('"%s" is not a valid check.', $check);
    }
    
    //Do the old stuff using the boolean returned by the given check method.
    return wrap($this->_not($this->{"is$check"}()));
  
  }
  
  //Extend the successable trait.
  public function success(callable $callback = null)
  {
    
    if(is_null($callback)){
      return $this->_success();
    }
    
    return wrap($this->_success($callback));
    
  }
  
  //Extend the successable trait.
  public function failure(callable $callback = null)
  {
    
    if(is_null($callback)){
      return $this->_failure();
    }
    
    return wrap($this->_failure($callback));
    
  }
  
}
