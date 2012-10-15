<?php namespace classes\sql;

class BuilderFunction
{
  
  //Private properties.
  private
    $builder,
    $function,
    $args=[],
    $data=[];
    
  //The constructor sets the function.
  public function __construct($function, Builder $builder)
  {
    
    //Must be a string.
    if(!is_string($function)){
      throw \exception\InvalidArgument('Expecting $function to be string. %s given.', typeof($function));
    }
    
    //Set.
    $this->function = strtoupper($function);
    $this->builder = $builder;
    
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
    
    //Prepare the input.
    $prepared = $this->builder->prepare($input);
    
    //If the input could not be prepared.
    if($prepared === false){
      $this->args[] = '?';
      $this->data[] = $input;
    }
    
    //Use the prepared input.
    else{
      $this->args[] = $prepared;
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Get the query-string.
  public function getString()
  {
    
    return "{$this->function}(".implode(', ', $this->args).')';
    
  }
  
  //Get the query data.
  public function getData()
  {
    
    return $this->data;
    
  }
  
}
