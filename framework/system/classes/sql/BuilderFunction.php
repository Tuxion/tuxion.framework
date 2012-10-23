<?php namespace classes\sql;

class BuilderFunction extends BaseBuilder
{
  
  //Private properties.
  private
    $function,
    $args=[];
    
  //The constructor sets the function.
  public function __construct($function, Builder $builder)
  {
    
    //Must be a string.
    if(!is_string($function)){
      throw \exception\InvalidArgument('Expecting $function to be string. %s given.', typeof($function));
    }
    
    //Set.
    $this->function = strtoupper($function);
    $this->setBuilder($builder);
    
  }
  
  //Set the arguments to be used in the function, of any.
  public function setArguments(array $args)
  {
    
    //Add them one by one.
    foreach($args as $arg){
      $this->addArgument($arg);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add an argument to our arguments.
  public function addArgument($input)
  {
    
    //Add the prepared argument.
    $this->args[] = $this->prepare($input);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Get the query-string.
  public function getString()
  {
    
    return "{$this->function}(".implode(', ', $this->args).')';
    
  }
  
}
