<?php namespace classes\sql;

use \classes\data\ArrayWrapper;

class Resultset extends ArrayWrapper
{
  
  public function __construct(array $rows=[])
  {
    
    $this->set($rows);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
