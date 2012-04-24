<?php namespace classes;

class Route
{
  
  //Private properties.
  private
    $pres=[],
    $end,
    $posts=[];
    
  //Public properties.
  public
    $type,
    $path;
    
  //The constructor sets the type and path.
  public function __construct($type, $path)
  {
    
    $this->type = $type;
    $this->path = $path;
    
  }
  
  //Add a preprocessor to this route.
  public function pre($description, \Closure $callback)
  {
    
    $this->pres[] = new \classes\RoutePreProcessor($description, $callback);
    
    return $this;
    
  }
  
  //Add an endpoint to this route.
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
  
  //Add a post-processor to this route.
  public function post($description, \Closure $callback)
  {
    
    $this->posts[] = new \classes\RoutePostProcessor($description, $callback);
    
    return $this;
    
  }
  
  //Return true if an endpoint has been set for this route.
  public function hasEnd()
  {
    
    return !empty($this->end);
    
  }
  
  //Call this route's preprocessors with the given arguments.
  public function _callPres()
  {
    
    foreach($this->pres as $pre){
      $pre->apply(func_get_args());
    }
    
  }
  
  //Call he endpoint of this route.
  public function _callEnd()
  {
  
    if(!$this->hasEnd()){
      throw new \exception\Programmer('No endpoint to call.');
    }
    
    $this->end->apply(func_get_args());
  
  }
  
  //Call this route's post-processors with the given arguments.
  public function _callPosts()
  {
    
    foreach($this->posts as $post){
      $post->apply(func_get_args());
    }
    
  }
  
}
