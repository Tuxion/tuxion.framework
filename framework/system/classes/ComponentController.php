<?php namespace classes;

class ComponentController extends Controller
{
  
  //Public properties.
  public
    $component,
    $filename;
    
  //Set all the properties.
  public function __construct($type, $root, $base, Component $com, $filename)
  {
    
    parent::__construct($type, $root, $base);
    $this->component = $com;
    $this->filename = $filename;
    
  }
  
  //Set the component.
  public function setComponent(Component $com)
  {
    
    $this->component = $com;
    return $this;
    
  }
  
  //Return a new controller, having it's base set at the given path relative to the base of this controller.
  public function getSubController()
  {
    
    //Handle Arguments.
    Router::handleArguments(func_get_args(), $type, $path);
    
    //Set type to default.
    $type = (is_null($type) ? $this->type : $type);
    
    //Make the path full.
    $path = $this->fullPath($path);
    
    //Make the key.
    $key = "$type:$path";
    
    //See if this controller has been defined before.
    if(array_key_exists($key, self::$controllers)){
      return self::$controllers[$key];
    }
    
    //Make the controller.
    $r = (new $this($type, false, $path, $this->component, $this->filename))->setRouter($this->router);
    
    //Return the controller.
    return $r;
    
  }
  
  //Check the database to see if this end may overwrite the previous.
  public function end()
  {
    
    #TODO: Check the database to see if this end may overwrite the previous.
    return call_user_func_array('parent::end', func_get_args());
    
  }
  
}
