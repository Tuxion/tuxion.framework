<?php namespace classes;

class Templator extends \classes\data\ArrayWrapper
{
  
  //Always wrap nodes.
  public function arrayGet($key)
  {
    
    if(!$this->arrayExists($key)){
      return new data\Undefined;
    }
      
    if(is_array(parent::arrayGet($key))){
      return new self(parent::arrayGet($key));
    }
    
    return wrap(parent::arrayGet($key));
    
  }
  
}
