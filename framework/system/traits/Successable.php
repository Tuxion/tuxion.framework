<?php namespace traits;

trait Successable
{
  
  //Implement traits.
  use Readonly;
  
  private $_success=null;
  
  //Sets the success state to the boolean that is given, or returned by given callback.
  public function is($check)
  {
    
    $this->_success = $this->_doCheck($check);
    return $this;
    
  }
  
  //Sets the success state to the opposite of what $this->is() would set it to with the same arguments.
  public function not($check)
  {
    
    $this->_success = !$this->_doCheck($check);
    return $this;
    
  }
  
  //Combines the current success state with what the new success state would be if is() would be called with the given arguments.
  public function andIs($check)
  {
    
    if($this->_success === false){
      return $this;
    }
    
    return $this->is($check);
    
  }
  
  //Combines the current success state with what the new success state would be if not() would be called with the given arguments.
  public function andNot($check)
  {
    
    if($this->_success === false){
      return $this;
    }
    
    return $this->not($check);
    
  }
  
  //Returns true, or executes $callback($this) if $this->_success is true.
  public function success(callable $callback)
  {
  
    if($this->_success === true){
      $return = $callback($this);
      if(!is_null($return)) return $return;
    }
      
    return $this;
    
  }
  
  //Returns true, or executes $callback($this) if $this->_success is false.
  public function failure(callable $callback=null)
  {
    
    if($this->_success === false){
      $return = $callback($this);
      if(!is_null($return)) return $return;
    }
    
    return $this;
    
  }
  
  //Convert given $check to boolean.
  private function _doCheck($check)
  {
    
    if($check instanceof \Closure){
      return (bool) $check($this);
    }
    
    elseif(uses($check, 'Successable')){
      return $check->_success === true;
    }
    
    else{
      return (bool) $check;
    }
    
  }
  
}

