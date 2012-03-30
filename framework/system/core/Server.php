<?php namespace core;

class Server extends \classes\ArrayObject
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