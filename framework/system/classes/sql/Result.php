<?php namespace classes\sql;

use \classes\data\ArrayWrapper;

class Result extends ArrayWrapper
{
  
  public function __construct($rows=[])
  {
    
    $this->set($rows);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
