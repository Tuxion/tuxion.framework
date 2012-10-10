<?php namespace classes\sql\clauses;

abstract class BaseClause
{
  
  //Public properties.
  public
    $builder;
  
  //Protected properties.
  protected
    $data=[];
    
  //Set the builder and the type.
  public function __construct(\classes\sql\Builder $builder)
  {
    
    $this->builder = $builder;
    
  }
  
  //Forward to getString.
  public function __toString()
  {
    
    return $this->getString();
    
  }
  
  //Adds the string of this clause to the given string.
  public function extendString(&$string)
  {
    
    $string .= $this->getString();
    return $this;
    
  }
  
  //Adds the data of this clause to the given array.
  public function extendData(array &$data)
  {
    
    $data = array_merge($data, $this->getData());
    return $this;
    
  }
  
  //Return the string representation of this clause. To be extended.
  public function getString()
  {
    
    return '';
    
  }
  
  //Return the data that the string will need. To be extended.
  public function getData()
  {
    
    return $this->data;
    
  }
  
}
