<?php namespace classes\locators;

abstract class Base
{
  
  //Protected static properties.
  protected static
    $readonly = ['name', 'location'];
  
  //Protected properties.
  protected
    $location,
    $name,
    $parent;
  
  //Set the properties.
  public function __construct($name, $location, self $parent = null)
  {
    
    //Set a parent?
    if(!is_null($parent))
    {
      
      //Overwrite location?
      if(is_null($location)){
        $location = $this->getLocationByParent($parent);
      }
      
      //We need a relative location.
      if($location{0} === '/'){
        throw new \exception\Restriction('Location must be relative when a parent is given.');
      }
      
      //Set.
      $this->parent = $parent;
      
    }
    //Otherwise location must be a directory.
    elseif(!is_dir($location)){
      throw new \exception\ResourceMissing('Directory "%s" does not exist.', $location);
    }
    
    //Set.
    $this->location = $location;
    $this->name = $name;
    
  }
  
  //Return a read-only property.
  public function __get($key)
  {
    
    $readonly = [];
    
    if(isset(self::$readonly)){
      $readonly = array_merge($readonly, self::$readonly);
    }
    
    if(isset(static::$readonly)){
      $readonly = array_merge($readonly, static::$readonly);
    }
    
    if(!in_array($key, $readonly)){
      throw new \exception\Restriction('Property %s does not exist.', $key);
    }
    
    return $this->{$key};
    
  }
  
  //Create a sub-resource.
  public function __call($type, $args)
  {
    
    return tx('Resource')->getLocator(ucfirst($type), $args[0], null, $this);
    
  }
  
  //Return the full resource path.
  public function locate()
  {
    
    return (is_null($this->parent) ? '' : $this->parent->locate().'/').$this->getLocation().$this->name;
    
  }
  
  //Method should return a path segment based on what their parent is.
  abstract protected function getLocationByParent(Base $parent);
  
  //Checks if this locator is of the given type.
  public function is($type)
  {
    
    //Type must be a string.
    if(!is_string($type)){
      throw new \exception\InvalidArgument('Expecting $type to be string. %s given', typeof($type));
    }
    
    //Make the class string.
    $class = "\\classes\\locators\\$type";
    
    return $this instanceof $class;
    
  }
  
  //Checks if this locator is one of the given types.
  public function isAmongst()
  {
    
    $types = wrap(func_get_args())->flatten()->get();
    
    foreach($types as $type){
      if($this->is($type)){
        return true;
      }
    }
    
    return false;
    
  }
  
  //Checks if this locator is not of given type.
  public function not($type)
  {
    
    return !$this->is($type);
    
  }
  
  //Checks if this locator is not one of the given types.
  public function notAmongst()
  {
    
    $types = wrap(func_get_args())->flatten()->get();
    
    foreach($types as $type){
      if($this->is($type)){
        return false;
      }
    }
    
    return true;
    
  }
  
  //Returns the location plus an appropriate trailing slash.
  public function getLocation()
  {
    
    return (is_null($this->location) ? '' : "{$this->location}/");
    
  }
  
}
