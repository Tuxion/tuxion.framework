<?php namespace classes\route;

use \classes\Materials;

class Controller
{
  
  //Protected properties.
  protected
    $pres=[],
    $end,
    $posts=[];
    
  //Public properties.
  public
    $type=15,
    $path,
    $root=false;
  
  //The constructor sets the type, root, path and router.
  public function __construct($type, $path, $root = false)
  {
    
    $this->path = $path;
    $this->root = $root;
    
    if(is_int($type)){
      $this->type = $type;
    }
    
  }
  
  //Add a preprocessor to this controller.
  public function pre($description, \Closure $callback)
  {
    
    //Create the processor.
    $this->pres[] = new PreProcessor($description, $callback);
    
    //Enable chaining.
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
    $this->end = new EndPoint($description, $callback);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add a post-processor to this controller.
  public function post($description, \Closure $callback)
  {
    
    //Create the processor.
    $this->posts[] = new PostProcessor($description, $callback);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return true if an endpoint has been set for this controller.
  public function hasEnd()
  {
    
    return !empty($this->end);
    
  }
  
  //Call this controller's preprocessors with the given arguments.
  public function callPres(Materials $materials, array $params)
  {
    
    //Execute every preprocessor.
    foreach($this->pres as $pre){
      $pre->execute($materials, $params);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return the endpoint of this Controller.
  public function getEnd()
  {
    
    //Do we even have one?
    if(!$this->hasEnd()){
      throw new \exception\ResourceMissing('No endpoint to return.');
    }
    
    //Yep.
    return $this->end;
  
  }
  
  //Call this controller's post-processors with the given arguments.
  public function callPosts(Materials $materials, array $params)
  {
    
    //Execute every preprocessor.
    foreach($this->posts as $post){
      $post->execute($materials, $params);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Alias of getSubController()
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
    
    //A sub-controller with a type that doesn't fit in the parent controller will never work.
    if( ! wrap($this->type)->hasBit($type)){
      throw new \exception\Restriction(
        'You can not make sub-controllers with type %s while the parent controller has type %s.',
        $type, $this->type
      );
    }
    
    //Make the path full.
    $path = $this->fullPath($path);
    
    //Return the controller.
    return tx('Controllers')->get($type, $path);
    
  }
  
  //Run a closure in which route() uses $this as context.
  public function run(\Closure $cb)
  {
    
    tx('Controllers')->when($this->type, $this->path, $cb);
    
    return $this;
    
  }
  
  //Return the cleaned path, appended to the relative or absolute root.
  protected function fullPath($path='')
  {
    
    //Empty path.
    if(empty($path)){
      return $this->path;
    }
    
    //Absolute path.
    if($path{0} == '/')
    {
      
      //Is this object allowed to use absolute paths?
      if(!$this->root){
        throw new \exception\Restriction('You can not use absolute paths here, you tried: "%s".', $path);
      }
      
      //Return the cleaned path.
      return path($this->root.$path)->clean()->get();
      
    }
    
    //Relative path.
    return path($this->path.'/'.$path)->clean()->get();
    
  }
  
}
