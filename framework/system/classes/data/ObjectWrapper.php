<?php namespace classes\data;

class ObjectWrapper extends BaseData
{
  
  //Private properties.
  private
    $object;
  
  //Validate and set the value.
  public function __construct($value)
  {
    
    raw($value);
    
    if(!(is_object($value)){
      throw new \exception\InvalidArgument('Expecting $value to be an object. %s given.', typeof($value));
    }
    
    $this->object = $value;
    
  }
  
  //Cast the object to string.
  public function toString()
  {
    
    return new StringWrapper('[data\Object]');
    
  }
  
  //Get the variables in the object as a wrapped array.
  public function vars()
  {
    
    return new ArrayWrapper(get_object_vars($this->object));
    
  }
  
  //Returns a wrapped string containing the class name of the object.
  public function class()
  {
    
    return new StringWrapper(get_class($this->object));
    
  }
  
  //Returns a wrapped string containing the class name of the object without namespace.
  public function baseclass()
  {
    
    return new StringWrapper(baseclass(get_class($this->object)));
    
  }
  
  //Returns true if this objects class uses the given trait.
  public function uses($trait_name)
  {
    
    //Start with the object.
    $object = $this->object;
    
    //Get the traits used by its class.
    do{
      if(array_key_exists("traits\\$trait_name", class_uses($object))){
        return true;
      }
    }
    
    //Move to the parent class.
    while($object = get_parent_class($object));
    
    //Nope.
    return false;
    
  }

  
}
