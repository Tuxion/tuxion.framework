<?php namespace traits;

trait Successable
{
  
  protected $success=null;
  
  //Sets the success state to the boolean that is given, or returned by given callback.
  public function is($check)
  {
    
    $this->success = $this->_doCheck($check);
    return $this;
    
  }
  
  //Sets the success state to the opposite of what $this->is() would set it to with the same arguments.
  public function not($check)
  {
    
    $this->success = !$this->_doCheck($check);
    return $this;
    
  }
  
  //Combines the current success state with what the new success state would be if is() would be called with the given arguments.
  public function andIs($check)
  {
    
    if($this->success === false){
      return $this;
    }
    
    return $this->is($check);
    
  }
  
  //Combines the current success state with what the new success state would be if not() would be called with the given arguments.
  public function andNot($check)
  {
    
    if($this->success === false){
      return $this;
    }
    
    return $this->not($check);
    
  }
  
  //Returns true, or executes $callback($this) if $this->success is true.
  public function success(callable $callback=null)
  {
    
    if(is_null($callback)){
      return $this->success;
    }
    
    if($this->success === true){
      return $this->_doCallback($callback);
    }
      
    return $this;
    
  }
  
  //Returns true, or executes $callback($this) if $this->success is false.
  public function failure(callable $callback=null)
  {
    
    if(is_null($callback)){
      return !$this->success;
    }
    
    if($this->success === false){
      return $this->_doCallback($callback);
    }
    
    return $this;
    
  }
  
  //Executes a callback and returns its return value if it has one. Returns $this otherwise.
  public function _doCallback(callable $callback)
  {
    
    #TODO: Use reflection to detect if there is a return value. $reflect = new \ReflectionFunction($callback);
    
    //Get the return value.
    $return = $callback($this);
    
    //Return this or the value.
    return (is_null($return) ? $this : $return);
    
  }
  
  //Convert given $check to boolean.
  private function _doCheck($check)
  {
    
    if($check instanceof \Closure){
      return (bool) $check($this);
    }
    
    elseif(is_object($check) && wrap($check)->uses('Successable')->isTrue()){
      return $check->success === true;
    }
    
    else{
      return (bool) $check;
    }
    
  }
  
}

