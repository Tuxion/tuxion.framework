<?php namespace classes;

class Controller
{
  
  //Private static properties.
  private static
    $controllers=[],
    $callbacks=[];
  
  //Return all controllers with optional filtering.
  public static function controllers()
  {
    
    //No filters.
    if(func_num_args() == 0){
      return self::$controllers;
    }
    
    //Prepare variables.
    tx('Router')->_handleArguments(func_get_args(), $type, $path);
    $matches = [];
    
    //Exclusive filter.
    if(func_num_args() == 2)
    {
      
      $key = "$type:$path";
      
      if(array_key_exists($key, self::$controllers)){
        $matches[] = self::$controllers[$key];
      }
      
      return $matches;
      
    }
    
    //Iterate our controllers to filter them down.
    foreach(self::$controllers as $controller)
    {
      
      if(!is_null($path) && $path != $controller->path){
        continue;
      }
      
      if(!is_null($type) && !checkbit($type, $controller->type)){
        continue;
      }
      
      $matches[] = $controller;
      
    }
    
    return $matches;
    
  }
  
  //Reiterate the uncalled callbacks.
  public static function rerun()
  {
    
    foreach(self::$callbacks as $k => $with){
      if($with[1]->active()){
        $c = c();
        c($with[1]);
        $with[0]();
        c($c);
        unset(self::$callbacks[$k]);
      }
    }
    
  }
  
  //Private properties.
  private
    $pres=[],
    $end,
    $posts=[];
    
  //Public properties.
  public
    $type=15,
    $root=false,
    $base=null;
  
  //The constructor sets the type and base.
  public function __construct($type=null, $root=false, $base=null)
  {
    
    $this->base = $base;
    $this->root = $root;
    
    if(is_int($type)){
      $this->type = $type;
    }
    
    $key = "{$this->type}:{$this->base}";
    self::$controllers[$key] = $this;
    
  }
  
  //Add a preprocessor to this controller.
  public function pre($description, \Closure $callback)
  {
    
    $this->pres[] = new \classes\RoutePreProcessor($description, $callback);
    
    return $this;
    
  }
  
  //Add an endpoint to this controller.
  public function end()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //We need a callback!
    if(empty($args)){
      throw new \exception\InvalidArgument('Too few arguments given.');
    }
    
    //We have a callback! :D
    $callback = array_pop($args);
    
    //We need a description!
    if(empty($args)){
      throw new \exception\InvalidArgument('Too few arguments given.');
    }
    
    //We have a description! :D
    $description = array_pop($args);
    
    //Was an overwrite given?
    if(!empty($args)){
      $overwrite = array_shift($args);
    }
    
    //Nope.
    else{
      $overwrite = false;
    }
    
    //Should we set it?
    if(!empty($this->end) && !$overwrite){
      return $this;
    }
    
    //Yep.
    $this->end = new \classes\RouteEndPoint($description, $callback);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add a post-processor to this controller.
  public function post($description, \Closure $callback)
  {
    
    $this->posts[] = new \classes\RoutePostProcessor($description, $callback);
    
    return $this;
    
  }
  
  //Return true if an endpoint has been set for this controller.
  public function hasEnd()
  {
    
    return !empty($this->end);
    
  }
  
  //Call this controller's preprocessors with the given arguments.
  public function _callPres()
  {
    
    foreach($this->pres as $pre){
      $pre->apply(func_get_args());
    }
    
  }
  
  //Call he endpoint of this controller.
  public function _callEnd()
  {
  
    if(!$this->hasEnd()){
      throw new \exception\Programmer('No endpoint to call.');
    }
    
    $this->end->apply(func_get_args());
  
  }
  
  //Call this controller's post-processors with the given arguments.
  public function _callPosts()
  {
    
    foreach($this->posts as $post){
      $post->apply(func_get_args());
    }
    
  }
  
  //Alias of get()
  public function __invoke()
  {
    
    return call_user_func_array([$this, 'get'], func_get_args());
    
  }
  
  //Return the controller object for the route indicated by the given arguments.
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
    
    //See if this controller has been defined before.
    if(array_key_exists($key, self::$controllers)){
      return self::$controllers[$key];
    }
    
    //Make the controller.
    $class = get_class($this);
    $r = new $class($type, false, $path);
    
    //Return the controller.
    return $r;
    
  }
  
  //Run a closure in which c() uses $this as context.
  public function run(\Closure $cb)
  {
    
    //Call or store the callback?
    if($this->active()){
      $c = c();
      c($this);
      $cb();
      c($c);
    }else{
      self::$callbacks[] = [$cb, $this];
    }
    
    return $this;
    
  }
  
  //Returns true if this route is matched by the current page-load.
  public function active()
  {
    
    return tx('Router')->matchPath($this->base);
    
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
