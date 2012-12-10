<?php namespace classes\sql;

class Result extends \classes\data\ArrayWrapper
{
  
  public function __construct($rows=[])
  {
    
    $this->set($rows);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
