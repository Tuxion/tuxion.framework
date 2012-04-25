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
    
    $route = call_user_func_array("parent::get", func_get_args());
    
    if(substr_count($route->path, '/') < 1
    || strstr($route->path, '/', true) !== 'com'
    || explode('/', $route->path)[1][0] == '$'
    ){
      throw new \exception\Restriction(
        'Invalid route "%s". Your route must start with "com/<component_name>".',
        $route->path
      );
    }
    
    return new \classes\ComponentRoute($route);
    
  }
  
}
