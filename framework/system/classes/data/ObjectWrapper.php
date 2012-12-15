<?php namespace classes\data;

class ObjectWrapper extends BaseData
{
  
  //Private properties.
  private
    $object;
  
  //Validate and set the value.
  public function __construct($value)
  {
    
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
    
    if(!method_exists($this, "_public_$method")){
      throw new \exception\NonExistent('Class %s has no method %s.', get_class(), $method);
    }
    
    return call_user_func_array([$this, "_public_$method"], $arguments);
    
  }
  
  //Cast the object to string.
  public function toString()
  {
    
    return new StringWrapper('[data\Object]');
    
  }
  
  //Return the variables of this object in JSON format.
  public function toJSON()
  {
    
    return new StringWrapper(json_encode($this->vars()));
    
  }
  
  //Return a StringWraper containing the visual representation of this object.
  public function visualize()
  {
    
    return new StringWrapper('object('.$this->name().')');
    
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
    
    return new StringWrapper(substr_count($this->_public_class()->get(), '\\') > 0
      ? substr(strrchr($this->_public_class()->get(), '\\'), 1)
      : $this->_public_class()->get()
    );
    
  }
  
  //Returns true if this objects class uses the given trait.
  public function uses($trait_name)
  {
    
    //Start with the object.
    $object = $this->object;
    
    //Get the traits used by its class.
    do{
      if(array_key_exists("traits\\$trait_name", class_uses($object))){
        return new BooleanWrapper(true);
      }
    }
    
    //Move to the parent class.
    while($object = get_parent_class($object));
    
    //Nope.
    return new BooleanWrapper(false);
    
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
    
    return new NumberWrapper($id);

  }

  function name()
  {
    
    return new StringWrapper(get_class($this->object).'#'.$this->id());
    
  }
  
}
