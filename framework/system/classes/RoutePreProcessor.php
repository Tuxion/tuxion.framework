<?php namespace classes;

class RoutePreProcessor extends RouteProcessor
{
  
  //Reroutes to the new given route. ([$discardFuture=false, ]$path)
  public function reroute()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Are enough arguments given?
    if(empty($args)){
      throw new \exception\InvalidArgument('Too few arguments given.');
    }
    
    //get the path.
    $path = array_pop($args);
    
    //Validate the path.
    if(!is_string($path)){
      throw new \exception\InvalidArgument('Expecting $path to be string. %s given.', ucfirst(typeof($path)));
    }
    
    //Is a discard boolean given?
    if(!empty($args)){
      $discard = array_pop($args);
    }else{
      $discard = false;
    }
    
    //Validate the boolean.
    if(!is_bool($discard)){
      throw new \exception\InvalidArgument(
        'Expecting $discard to be boolean. %s given.',
        ucfirst(typeof($discard))
      );
    }
    
    //Reroute.
    tx('Router')->reroute($path, !$discard);
    
    //Reiterate the run callbacks blocks.
    \classes\Controller::rerun();
    
  }
  
  //Method description.
  public function permissions()
  {
    
    # code...
    
  }

}
