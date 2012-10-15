<?php namespace classes\sql;

class BuilderFunctionFactory
{
  
  //Private properties.
  private
    $builder;
  
  //The constructor sets the builder.
  public function __construct(Builder $builder)
  {
    
    $this->builder = $builder;
    
  }
  
  //This creates a BuilderFunction.
  public function __call($function_name, $args)
  {
    
    return (new BuilderFunction($function_name, $this->builder))->setArguments($args);
    
  }
  
}
