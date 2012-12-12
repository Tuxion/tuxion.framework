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
    
    if(!(is_object($value))){
      throw new \exception\InvalidArgument('Expecting $value to be an object. %s given.', typeof($value));
    }
    
    $this->object = $value;
    
  }
  
  //Return the object.
  public function get()
  {
    
    return $this->object;
    
  }
  
  //Implement some magic to bypass reserved words.
  public function __call($method, $arguments)
  {
    
    return call_user_func_array([$this, "_public_$method"], $arguments);
    
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
  private function _public_class()
  {
    
    return new StringWrapper(get_class($this->object));
    
  }
  
  //Returns a wrapped string containing the class name of the object without namespace.
  public function baseclass()
  {
    
    return new StringWrapper(substr(strrchr($this->_public_class(), '\\'), 1));
    
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
  
  function id()
  {
    
    static $object_ids = [];
    
    $hash = spl_object_hash($this->object);
    
    if(array_key_exists($hash, $object_ids)){
      $id = $object_ids[$hash];
    }
    
    else{
      $object_ids[$hash] = $id = (count($object_ids) + 1);
    }
    
    return $id;

  }

  function name()
  {
    
    return get_class($this->object).'#'.$this->id();
    
  }
  
}
