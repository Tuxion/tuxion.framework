<?php namespace classes;

class Router
{
  
  //Private static properties.
  private static
    $routes=[];
    
  //Private properties.
  private
    $type=15,
    $base=null;
  
  //Returns an instance of self.
  public static function create()
  {
    
    self::_handleArguments(func_get_args(), $type, $path);
    return new self($type, $path);
    
  }
  
  //Accepts an array with up to 2 arguments and returns an array with keys "type" and "path".
  private static function _handleArguments($args, &$type=null, &$path=null)
  {
    
    //Handle arguments.
    if(count($args) == 2){
      $type = $args[0];
      $path = $args[1];
    }
    
    //Only one argument has been given.
    elseif(count($args) == 1)
    {
      
      //Is it the type?
      if(is_int($args[0])){
        $type = $args[0];
        $path = null;
      }
      
      //Is it the path?
      elseif(is_string($args[0])){
        $type = null;
        $path = $args[0];
      }
      
      //It is NOTHING!
      else{
        throw new \exception\InvalidArgument('Expecting a string or integer. %s given.', ucfirst(typeof($args[0])));
      }
      
    }
    
    //An invalid amount of arguments were given.
    else{
      throw new \exception\InvalidArgument('Expecting one or two arguments. %s Given.', count($args));
    }
    
    return ['type' => $type, 'path' => $path];
    
  }
  
  //The constructor sets the base.
  public function __construct($type=null, $base=null)
  {
    
    $this->base = $base;
    
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
    $route = self::_handleArguments(func_get_args(), $type, $path);
    
    //Set type to default.
    $type = (is_null($type) ? $this->type : $type);
    
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
      throw new \exception\InvalidArgument('Expecting the last argument to be an instance of \Closure. %s given.', ucfirst(typeof($cb)));
    }
    
    //Handle the remaining arguments.
    $args = self::_handleArguments($args);
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
  public function is($path='')
  {
    
    return tx('Router')->matchPath("{$this->base}/$path");
    
  }
  
  //Returns false if the given path would match the current request in this Router.
  public function not($path='')
  {
    
    return (! $this->is($path));
    
  }
  
}
