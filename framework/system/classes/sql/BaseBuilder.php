<?php namespace classes\sql;

abstract class BaseBuilder
{
  
  //Private properties.
  private
    $data=[];
    
  //Public properties.
  public
    $builder;
  
  //Set the builder.
  public function setBuilder(Builder $builder)
  {
    
    $this->builder = $builder;
    
  }
  
  //Return the data.
  public function getData()
  {
    
    return $this->data;
    
  }
  
  //Extends the data.
  protected function addData(array $data)
  {
    
    //Extend.
    $this->data = array_merge($this->data, $data);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Prepare input and handle data.
  protected function prepare($input, &$data=null)
  {
    
    //Must have a builder set.
    if(!($this->builder instanceof Builder)){
      throw new \exception\Restriction('Can not prepare without having a builder.');
    }
    
    //Create the empty data array.
    $data = [];
    
    //Prepare the input and catch excess data.
    $prepared = $this->builder->prepare($input, $data);
    
    //Merge excess data.
    $this->addData($data);
    
    //Return the prepared result.
    return $prepared;
    
  }
  
}
