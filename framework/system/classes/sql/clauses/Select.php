<?php namespace classes\sql\clauses;

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
  public function addColumn($column, $alias=null)
  {
    
    //Prepare the content.
    $content = $this->builder->prepare($column);
    
    //If the content was a model, we'll select all the columns from it I guess..
    if($column instanceof \classes\sql\BuilderModel){
      $content .= '.*';
    }
    
    //If the content was a string.
    if($content === false)
    {
      
      //We must have an alias.
      if(is_null($alias)){
        throw new \exception\Programmer('Can not select "%s" without giving it an alias.', $column);
      }
      
      //Add the string to our data.
      $content = '?';
      $this->data[] = $column;
      
    }
    
    //Use an alias?
    if(!is_null($alias)){
      $this->builder->validateAlias($alias)->addAlias($alias);
      $content .= " AS `$alias`";
    }
    
    //Add the result to our columns.
    $this->columns[] = $content;
    
    //Enable chaining.
    return $this;
    
  }
  
}
