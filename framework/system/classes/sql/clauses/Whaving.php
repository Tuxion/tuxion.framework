<?php namespace classes\sql\clauses;

class Whaving extends BaseClause
{
  
  //Private properties.
  private
    $conditions=[];
  
  //Add a condition. Duh.
  public function addCondition(\classes\sql\BuilderCondition $condition)
  {
    
    $this->conditions[] = $condition;
    
  }
    
  //Return the conditions joined using AND's.
  public function getString()
  {
    
    //Get the name of the class.
    return strtoupper(substr(strrchr(get_class($this), '\\'), 1)).' '.
    
    //Wrap the conditions in Data.
    wrap($this->conditions)
    
    //Map the internal strings.
    ->map(function($cond){
      return $cond->get()->getString();
    })
    
    //Join them using AND's.
    ->join(' AND ')
    
    //Get the output.
    ->get();
    
  }
  
  //Extract the data from all the conditions.
  public function getData()
  {
    
    $data = [];
    
    foreach($this->conditions as $cond){
      $data = array_merge($data, $cond->getData());
    }
    
    return $data;
    
  }
  
}
