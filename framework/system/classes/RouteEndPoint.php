<?php namespace classes;

class RouteEndPoint extends RouteProcessor
{

  //Public properties.
  public
    $template=false;

  //Extend parent by allowing a template path.
  public function __construct($description, \Closure $callback, $template=false)
  {
    
    parent::__construct($description, $callback);
    $this->template = $template;
    
  }
  
  public function execute()
  {
    
    call_user_func_array('parent::execute', func_get_args());
    
  }

}
