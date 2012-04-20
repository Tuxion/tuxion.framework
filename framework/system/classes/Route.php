<?php namespace classes;

class Route
{
  
  //Private properties.
  private
    $pres=[],
    $ends=[],
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
  public function pre(\Closure $callback)
  {
    
    $this->pres[] = new \classes\RoutePreProcessor($callback);
    
    return $this;
    
  }
  
  //Add an endpoint to this route.
  public function end(\Closure $callback)
  {
    
    $this->pres[] = new \classes\RouteEndPoint($callback);
    
    return $this;
    
  }
  
  //Add a post-processor to this route.
  public function post(\Closure $callback)
  {
    
    $this->pres[] = new \classes\RoutePostProcessor($callback);
    
    return $this;
    
  }
  
  //Call this route's preprocessors with the given arguments.
  public function _callPres()
  {
    
    foreach($this->pres as $pre){
      $pre->apply(func_get_args());
    }
    
  }
  
  //Call this route's post-processors with the given arguments.
  public function _callPosts()
  {
    
    foreach($this->posts as $post){
      $post->apply(func_get_args());
    }
    
  }
  
}
