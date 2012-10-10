<?php namespace classes\sql\clauses;

abstract class Grourder extends BaseClause
{
  
  //Private properties.
  private
    $by=[];
  
  //Extend the parents functionality.
  public function getString()
  {
    
    return strtoupper(substr(strrchr(get_class($this), '\\'), 1)).' BY '.implode(', ', $this->by);
    
  }
  
  //Add more to the clause.
  public function by($input, $direction=null)
  {
    
    //Prepare the input.
    $prepared = $this->builder->prepare($input);
    
    //If input was a string, add it to our data.
    if($prepared === false){
      $prepared = '?';
      $this->data[] = $input;
    }
    
    //Convert directions.
    if(is_int($direction)){
      $directions = [-1 => 'DESC', 0 => null, 1 => 'ASC'];
      $direction = $directions[$direction];
    }
    
    //Create direction string.
    $direction = (is_null($direction) ? '' : " $direction");
    
    //Add it to the clause.
    $this->by[] = "$prepared$direction";
    
  }
  
}
