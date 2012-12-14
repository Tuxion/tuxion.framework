<?php namespace classes;

use \classes\data\ArrayWrapper;

class Templator extends ArrayWrapper
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
