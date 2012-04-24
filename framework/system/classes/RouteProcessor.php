<?php namespace classes;

abstract class RouteProcessor
{
  
  //Private properties.
  private
    $called=false,
    $description,
    $callback;
  
  //Store the given callback.
  public function __construct($description, \Closure $callback)
  {
    
    //Validate argument.
    if(!is_string($description)){
      throw new \exception\InvalidArgument(
        'Expecting $description to be string. %s given.',
        ucfirst(typeof($description))
      );
    }
    
    //Set properties.
    $this->description = $description;
    $this->callback = $callback->bindTo($this);
    
  }
  
  //Give some programmer feedback to prevent confusion when working with pre-, post- and end-processors.
  public function __call($key, $args)
  {
    
    $allowed = [];
    
    foreach(['RoutePreProcessor', 'RoutePostProcessor', 'RouteEndPoint'] as $class)
    {
      
      if(method_exists($class, $key)){
        $allowed = $class;
      }
      
    }
    
    if(empty($allowed)){
      throw new \exception\Programmer('There is no controller method called %s.', $key);
    }
    
    throw new \exception\Programmer('The %s method can only be used in %s.', $key, implode(' and ', $allowed));
    
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
    
    //Create the userFunction.
    $func = new \classes\UserFunction($this->description, $this->callback);
    
    
    $this->called = true;
    
    //Enable chaining.
    return $this;
    
  }
  
}
