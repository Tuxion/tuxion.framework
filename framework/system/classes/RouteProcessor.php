<?php namespace classes;

abstract class RouteProcessor
{
  
  //Private properties.
  private
    $called=false,
    $callback,
    $return;
  
  //Store the given callback.
  public function __construct(\Closure $callback)
  {
    
    $this->callback = $callback->bindTo($this);
    
  }
  
  //Call the associated callback with the given arguments.
  public function call()
  {
    
    return $this->apply(func_get_args());
    
  }
  
  //Call the associated callback with the arguments in given array.
  public function apply(array $args=[])
  {
    
    //Make sure the callback is not executed twice.
    if($this->called){
      throw new \exception\Restriction('Can not call a RouteProcessor callback more than once.');
    }
    
    
    $this->called = true;
    $cb =& $this->callback;
    $this->return = call_user_func_array($cb, $args);
    
    return $this;
    
  }
  
}
