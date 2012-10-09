<?php namespace classes\sql;

class QueryBuilderColumn
{
  
  //Private properties.
  private
    $name='';
    
  //Public properties.
  public
    $model;
    
  //Construct using a name and a model.
  public function __construct($name, QueryBuilderModel $model)
  {
    
    $this->model = $model;
    $this->setName($name);
    
  }
  
  //Set the name after validating it.
  public function setName($name)
  {
    
    //It must be a string.
    if(!is_string($name)){
      throw new \exception\InvalidArgument('Expecting $name to be string. %s given.', typeof($name));
    }
    
    //It must be of the following format.
    if($name == '*' || preg_match('~^[a-zA-Z0-9_]+$~', $name) !== 1){
      throw new \exception\InvalidArgument('The column name does not have the right format.');
    }
    
    //Set.
    $this->name = $name;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return the string for insertion into a query.
  public function getString()
  {
    
    if($this->name == '*'){
      return $this->name;
    }
    
    return "`{$this->name}`";
    
  }
  
}
