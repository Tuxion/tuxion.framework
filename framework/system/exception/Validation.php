<?php namespace exception;

class Validation extends BadRequest
{

  protected static $ex_code = EX_VALIDATION;
  
  private
    $key,
    $value,
    $title,
    $errors = array();
    
  public function key($set=null)
  {
    
    if(is_null($this->key)) $this->key = is_null($set) ? '' : $set;
    
    return $this->key;
    
  }

  public function value($set=null)
  {
    
    if(is_null($this->value)) $this->value = is_null($set) ? '' : $set;
    
    return $this->value;
    
  }

  public function title($set=null)
  {
    
    if(is_null($this->title)) $this->title = is_null($set) ? '' : $set;
    
    return $this->title;
    
  }

  public function errors(array $set=null)
  {
    
    if(is_null($this->errors)) $this->errors = is_null($set) ? array() : $set;
    
    return $this->errors;
    
  }

}
