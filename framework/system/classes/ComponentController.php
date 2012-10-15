<?php namespace classes;

class ComponentController extends Controller
{
  
  //Public properties.
  public
    $component,
    $filename;
    
  //Set all the properties.
  public function __construct($type, $root, $base, Router $router, Component $com, $filename)
  {
    
    parent::__construct($type, $root, $base, $router);
    $this->component = $com;
    $this->filename = $filename;
    
  }
  
  //Return a new controller, having it's base set at the given path relative to the base of this controller.
  public function getSubController()
  {
    
    //Handle Arguments.
    Router::handleArguments(func_get_args(), $type, $path);
    
    //Set type to default.
    $type = (is_null($type) ? $this->type : $type);
    
    //A sub-controller with a type that doesn't fit in the parent controller will never work.
    if(!checkbit($type, $this->type)){
      throw new \exception\Programmer(
        'You made a sub-controller with type %s in a parent controller with type %s.',
        $type, $this->type
      );
    }
    
    //Make the path full.
    $path = $this->fullPath($path);
    
    //Make the controller.
    $r = (new $this($type, false, $path, $this->router, $this->component, $this->filename));
    
    //Add it to the router.
    if($r->active()){
      $this->router->addController($r);
    }
    
    //Return the controller.
    return $r;
    
  }
  
}
