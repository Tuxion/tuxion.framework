<?php namespace core;

use \classes\data\ArrayWrapper;

class Server extends ArrayWrapper
{
  
  public function __construct()
  {
    
    $vars = [];
    
    foreach($_SERVER as $key => $val){
      $vars[strtolower($key)] = $val;
    }
    
    parent::__construct($vars);
    unset($_SERVER);
    $this->setArrayPermissions(1,0,0);
    
  }
  
}
