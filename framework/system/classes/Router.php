<?php namespace classes;

class Router
{
  
  //Private static properties.
  private static
    $routes=[];
    
  //Private properties.
  private
    $type=15,
    $root=false,
    $base=null;
  
  //Return all routes with optional filtering.
  public static function routes()
  {
    
    //No filters.
    if(func_num_args() == 0){
      return self::$routes;
    }
    
    //Prepare variables.
    tx('Router')->_handleArguments(func_get_args(), $type, $path);
    $matches = [];
    
    //Iterate our routes to filter them down.
    foreach(self::$routes as $key => $route)
    {
      
      if(!is_null($path) && $path != $route->path){
        continue;
      }
      
      if(!is_null($type) && !checkbit($type, $route->type)){
        continue;
      }
      
      $matches[$key] = $route;
      
    }
    
    return $matches;
    
  }
  
  //The constructor sets the base.
  public function __construct($type=null, $base=null, $root=false)
  {
    
    $this->base = $base;
    $this->root = $root;
    
    if(is_int($type)){
      $this->type == $type;
    }
    
  }
  
  //Alias of get()
  public function __invoke()
  {
    
    return call_user_func_array([$this, 'get'], func_get_args());
    
  }
  
  //Return the route object that represents the route indicated by the given arguments.
  public function get()
  {
    
    //Handle Arguments.
    tx('Router')->_handleArguments(func_get_args(), $type, $path);
    
    //Set type to default.
    $type = (is_null($type) ? $this->type : $type);
    
    //Make the path full.
    $path = $this->fullPath($path);
    
    //Make the key.
    $key = "$type:$path";
    
    //See if this route has been defined before.
    if(array_key_exists($key, self::$routes)){
      return self::$routes[$key];
    }
    
    //Make the route.
    self::$routes[$key] = $r = new \classes\Route($type, $path);
    
    //Return the route.
    return $r;
    
  }
  
  //Call given callback with a new self having the given base appended to this base.
  public function with()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Last argument must always be the callback.
    $cb = array_pop($args);
    
    //Validate the callback.
    if(!($cb instanceof \Closure)){
      throw new \exception\InvalidArgument(
        'Expecting the last argument to be an instance of \Closure. %s given.',
        ucfirst(typeof($cb))
      );
    }
    
    //Handle the remaining arguments.
    $args = tx('Router')->_handleArguments($args);
    $path = $args['path'];
    $type = (is_null($args['type']) ? $this->type : $args['type']);
    
    //Validate if we are going to need to call the closure at all.
    if($this->not($path)){
      return $this;
    }
    
    //Make the new base path.
    $path = tx('Router')->cleanPath($this->base.'/'.$path);
    
    //Create the closure in the right context and call it.
    $context = new self($this->type, $path);
    $cb = $cb->bindTo($context);
    $cb();
    
    //Enable chaining.
    return $this;
    
  }
  
  //Returns true if the given path would match the current request in this Router. 
  public function is()
  {
    
    tx('Router')->_handleArguments(func_get_args(), $type, $path);
    return tx('Router')->matchPath($type, $this->fullPath($path));
    
  }
  
  //Returns false if the given path would match the current request in this Router.
  public function not($path='')
  {
    
    return (! $this->is($path));
    
  }
  
  private function fullPath($path='')
  {
    
    //Empty path.
    if(empty($path)){
      return $this->base;
    }
    
    //Absolute path.
    if($path{0} == '/')
    {
      
      //Is this object allowed to use absolute paths?
      if($this->root === false){
        throw new \exception\Restriction('You can not use absolute paths here, you tried: "%s".', $path);
      }
      
      //Return the cleaned path.
      return tx('Router')->cleanPath($this->root.$path);
      
    }
    
    //Relative path.
    return $this->base.'/'.tx('Router')->cleanPath($path);
    
  }
  
}
