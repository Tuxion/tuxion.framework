<?php namespace classes\sql\clauses;

abstract class BaseClause extends \classes\sql\BaseBuilder
{
  
  //Set the builder and the type.
  public function __construct(\classes\sql\Builder $builder)
  {
    
    $this->setBuilder($builder);
    
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
  
  //Return the string representation of this clause.
  abstract public function getString();
  
}
