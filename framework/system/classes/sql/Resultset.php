<?php namespace classes\sql;

class Resultset extends \classes\data\ArrayWrapper
{
  
  public function __construct(array $rows=[])
  {
    
    $this->set($rows);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
