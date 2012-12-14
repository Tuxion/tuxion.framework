<?php namespace classes\sql\clauses;

use \classes\sql\BuilderModel;

class Select extends BaseClause
{
  
  //Private properties.
  private
    $columns=[];
    
  //Extend the base functionality.
  public function getString()
  {
    
    return "SELECT ".implode(', ', $this->columns);
    
  }
  
  //Add a column to the select clause.
  public function addColumn($input, $alias=null)
  {
    
    //Prepare the content.
    $prepared = $this->prepare($input, $data);
    
    //If the input was a model, we'll select all the columns from it I guess..
    if($input instanceof BuilderModel){
      $prepared .= '.*';
    }
    
    //If there is data involved we must have an alias.
    if(!empty($data) && is_null($alias)){
      throw new \exception\Restriction('Can not select "%s" without giving it an alias.', $input);
    }
    
    //Use an alias?
    if(!is_null($alias)){
      $this->builder->validateAlias($alias)->addAlias($alias);
      $prepared .= " AS `$alias`";
    }
    
    //Add the result to our columns.
    $this->columns[] = $prepared;
    
    //Enable chaining.
    return $this;
    
  }
  
}
