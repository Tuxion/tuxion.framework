<?php namespace classes;

class SqlResultset extends ArrayObject
{
  
  public function __construct(array $rows=[])
  {
    
    $this->set($rows);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
