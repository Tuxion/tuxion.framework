<?php namespace classes;

class ComponentRouter extends Router
{
  
  //Private properties.
  private
    $component;
  
  //Extend parent constructor by forcing a component object to be passed.
  public function __construct($type=null, $base=null, $route=null, Component $component)
  {
    
    $this->component = $component;
    parent::__construct($type, $base, $route);
    
  }
  
  //Extend the parent get() function to return ComponentRoute's instead.
  public function get()
  {
    
    return new \classes\ComponentRoute(call_user_func_array("parent::get", func_get_args()));
    
  }
  
}
