<?php namespace classes\sql\clauses;

class Group extends Grourder
{
  
  //Private properties.
  private
    $rollup=false;
  
  //Extend parent functionality to include roll-ups.
  public function getString()
  {
    
    $string = parent::getString();
    
    if($this->rollup){
      $string .= ' WITH ROLLUP';
    }
    
    return $string;
    
  }
  
  //Add a roll-up modifier.
  public function withRollup()
  {
    
    $this->rollup = true;
    
  }
  
}
