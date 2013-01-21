<?php

//Set or invoke a Controller object.
function route(){
  
  static $c=null;
  
  if(func_num_args() == 0){
    return $c;
  }
  
  if(func_num_args() == 1
  &&(func_get_arg(0) instanceof \classes\route\Controller || is_null(func_get_arg(0)))
  ){
    return $c = func_get_arg(0);
  }
  
  if(is_null($c)){
    throw new \exception\Restriction('You can not use the route function here.');
  }
  
  return call_user_func_array([$c, 'getSubController'], func_get_args());
  
}

//Alias for tx('Request')->makeUrl($input)
function murl($input)
{
  
  return tx('Request')->makeUrl($input);
  
}
