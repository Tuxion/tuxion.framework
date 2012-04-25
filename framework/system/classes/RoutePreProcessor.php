<?php namespace classes;

class RoutePreProcessor extends RouteProcessor
{
  
  //Reroutes to the new given route. ([$discardFuture=false, ]$path)
  public function reroute()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    if(empty($args)){
      throw new \exception\InvalidArgument('Too few arguments given.');
    }
    
    $path = array_pop($args);
    
    if(!is_string($path)){
      throw new \exception\InvalidArgument('Expecting $path to be string. %s given.', ucfirst(typeof($path)));
    }
    
    if(!empty($args)){
      $discard = array_pop($args);
    }else{
      $discard = false;
    }
    
    if(!is_bool($discard)){
      throw new \exception\InvalidArgument('Expecting $discard to be boolean. %s given.', ucfirst(typeof($discard)));
    }
    
    tx('Router')->reroute($path, !$discard);
    
  }
  
  //Method description.
  public function permissions()
  {
    
    # code...
    
  }

}
