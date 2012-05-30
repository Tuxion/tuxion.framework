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
    Router::handleArguments(func_get_args(), $type, $path);
    $matches = [];
    
    //Iterate our controllers to filter them down.
    foreach(self::$controllers as $controller)
    {
      
      //Should we filter on path?
      if(!is_null($path))
      {
        
        //Get keys and values.
        $keys = explode('/', $controller->base);
        $values = explode('/', $path);
        
        //The routes must be of the same length.
        if(count($keys) !== count($values)){
          continue;
        }
        
        //Iterate the keys and values, and check whether they match.
        while( (list(,$key) = each($keys)) && (list(,$value) = each($values)) ){
          if(!($key{0} == '$' || $key === $value)){
            continue 2;
          }
        }
        
      }
      
      //Should we filter on type?
      if(!is_null($type) && !checkbit($type, $controller->type)){
        continue;
      }
      
      //Filters passed. Add to results.
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
    $router,
    $template,
    $pres=[],
    $posts=[];
    
  //Public properties.
  public
    $end,
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
    $this->end = new \classes\RouteEndPoint($description, $callback, $this->template);
    
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
  public function callPres(DataBranch $input, array $params)
  {
    
    foreach($this->pres as $pre){
      $pre->setProperties(['input' => $input]);
      $pre->setarguments($params);
      $pre->controller = $this;
      $pre->execute();
    }
    
    return $this;
    
  }
  
  //Call he endpoint of this controller.
  public function callEnd(DataBranch $input, DataBranch $output, array $params)
  {
    
    
    if(!$this->hasEnd()){
      throw new \exception\Programmer('No endpoint to call.');
    }
      
    $this->end->setProperties([
      'input' => $input,
      'output' => $output
    ]);
    $this->end->setarguments($params);
    $this->end->controller = $this;
    $this->end->execute();
    
    return $this;
  
  }
  
  //Call this controller's post-processors with the given arguments.
  public function callPosts(DataBranch $input, DataBranch $output, array $params)
  {
    
    foreach($this->posts as $post)
    {
      
      $post->setProperties([
        'input' => $input,
        'output' => $output
      ]);
      $post->setarguments($params);
      $post->controller = $this;
      $post->execute();
      
    }
    
    return $this;
    
  }
  
  //Alias of get()
  public function __invoke()
  {
    
    return call_user_func_array([$this, 'getSubController'], func_get_args());
    
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
    $class = get_class($this);
    $r = (new $class($type, false, $path))->setRouter($this->router);
    
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
  
  //Return true if the path set in this controller matches the path in the router calling this controller.
  public function active()
  {
    
    if(!($this->router instanceof Router)){
      throw new \exception\Unexpected('No router set in this controller.');
    }
    
    return $this->router->match($this->type, $this->base);
    
  }
  
  //Set the router.
  public function setRouter(Router $router)
  {
    
    $this->router = $router;
    return $this;
    
  }
  
  //Set the path to the template that should be used for endpoints made by this controller.
  public function setTemplate($path)
  {
    
    $this->template = $path;
    
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
      return Router::cleanPath($this->root.$path);
      
    }
    
    //Relative path.
    return Router::cleanPath($this->base.'/'.$path);
    
  }
  
}
