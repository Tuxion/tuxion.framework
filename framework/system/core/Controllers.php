<?php namespace core;

use \classes\route\Controller;
use \classes\route\Router;

class Controllers
{
  
  //Private properties.
  private
    $controllers=[],
    $callbacks=[];
  
  //Add more controllers to our controllers.
  public function addAll(array $controllers)
  {
    
    //Add them one by one.
    foreach($controllers as $controller){
      $this->add($controller);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add a controller to our array of controllers.
  public function add(Controller $controller)
  {
    
    $this->controllers[] = $controller;
    return $controller;
    
  }
  
  //Get the controllers stored under the given path.
  public function getAll()
  {
    
    //No filters.
    if(func_num_args() == 0){
      return $this->controllers;
    }
    
    //Prepare variables.
    Router::handleArguments(func_get_args(), $type, $path);
    $matches = [];
    
    
    //Iterate our controllers to filter them down.
    foreach($this->controllers as $controller)
    {
      
      //Should we filter on path?
      if(!is_null($path))
      {
        
        //Needs to be an exact match.
        if(substr_count($path, '/') !== substr_count($controller->path, '/')){
          continue;
        }
        
        //If the path doesn't match.
        if( ! path($path)->isMatch(path($controller->path)) ){
          continue;
        }
        
      }
      
      //Should we filter on type?
      if(!is_null($type) && ! wrap($controller->type)->hasBit($type)){
        continue;
      }
      
      //Filters passed. Add to results.
      $matches[] = $controller;
      
    }
    
    return $matches;
    
  }
    
  //Factory: retrieve or create a Controller.
  public function get($type=15, $path)
  {
    
    //Iterate the controllers.
    foreach($this->controllers as $controller)
    {
      
      //Match type.
      if($controller->type != $type){
        continue;
      }
      
      //Match path.
      if($controller->path != $path){
        continue;
      }
      
      //Full match.
      return $controller;
      
    }
    
    //Create, cache and return a new Controller.
    return $this->add(new Controller($type, $path));
    
  }
  
  //Run all callbacks associated with the given route.
  private function runCallbacks($type, $path)
  {
    
    //Split the path up into segments.
    $segments = explode('/', $path);
    
    #TODO: The rest of this function.. If it's worth it.
    
  }
  
}
