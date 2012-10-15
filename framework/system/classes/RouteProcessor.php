<?php namespace classes;

abstract class RouteProcessor
{
  
  //Private properties.
  private
    $properties=[],
    $arguments=[],
    $description,
    $callback;
    
  //Public properties.
  public
    $controller=null;
  
  //Store the given callback.
  public function __construct($description, \Closure $callback, Controller $controller = null)
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
    $this->controller = $controller;
    
  }
  
  //Return one of the variable properties.
  public function __get($key)
  {
    
    //Does the property exist?
    if(!array_key_exists($key, $this->properties)){
      throw new \exception\Programmer('Property "%s" does not exist in %s.', $key, get_object_name($this));
    }
    
    //Return it.
    return $this->properties[$key];
    
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
      throw new \exception\Programmer('There is no processor method named "%s".', $key);
    }
    
    throw new \exception\Programmer(
      'The %s method can only be used in %s.',
      $key, implode(' and ', $allowed)
    );
    
  }
  
  //Set an outer template inside the controller.
  public function template($name, $data)
  {
    
    $this->controller->router->outer_template = tx('Config')->paths->templates .'/'. $name;
    
  }
  
  //Validate using the Validator class.
  public function validate($data, $rules)
  {
    
    return new Validator($data, $rules);
    
  }
  
  //Call the associated callback with the arguments in given array.
  public function execute()
  {
    
    //Define execution tracker.
    static $executing=false;
    
    //Detect nested execution. That would be bad!
    if($executing){
      throw new \exception\Programmer(
        'Nested execution occurred; %stried %s while %s.',
        ($this->description == $executing ? 'yo dawg, you ' : ''),
        strtolower(trim($this->description, ' .!?')),
        strtolower(trim($executing, ' .!?'))
      );
    }
    
    //We are now executing the following:
    $executing = $this->description;
    
    //Create the userFunction.
    $func = new \classes\UserFunction($this->description, $this->callback, $this->arguments);
    
    //No longer executing.
    $executing = false;
    
    #TEMP:
    echo $func->output;
    
    //Enable chaining.
    return $func;
    
  }
  
  //Set the properties that can be accessed during execution.
  public function setProperties(array $properties)
  {
    
    $this->properties = $properties;
    
  }
  
  //Set the arguments that will be passed to the callback during execution.
  public function setArguments(array $arguments)
  {
    
    $this->arguments = $arguments;
    
  }
  
}
