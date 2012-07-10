<?php namespace classes;

class SqlResult extends ArrayObject
{
  
  public function __construct($rows=[])
  {
    
    $this->set($rows);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
