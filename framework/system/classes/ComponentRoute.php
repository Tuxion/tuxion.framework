<?php namespace classes;

class ComponentRoute
{
  
  //Private properties.
  private
    $route;
    
  //Set the route for which this object is a shell.
  public function __construct(Route $route)
  {
    
    $this->route = $route;
    
  }
  
  //Call forwarding.
  public function __call($key, $args)
  {
    
    return call_user_func_array([$this->route, $key], $args);
    
  }
  
  //Get forwarding.
  public function __get($key)
  {
    
    return $this->route->{$key};
    
  }
  
  //Check the database to see if this end may overwrite the previous.
  public function end($description, \Closure $callback)
  {
    
    
    
  }

}
