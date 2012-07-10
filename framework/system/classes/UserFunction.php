<?php namespace classes;

class UserFunction
{
  
  //Trait includes.
    public $success=null;
  
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
  public function success(callable $callback)
  {
  
    if($this->success === true){
      $return = $callback($this);
      if(!is_null($return)) return $return;
    }
      
    return $this;
    
  }
  
  //Returns true, or executes $callback($this) if $this->success is false.
  public function failure(callable $callback=null)
  {
    
    if($this->success === false){
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
      return $check->success === true;
    }
    
    else{
      return (bool) $check;
    }
    
  }
  
  //Public properties.
  public
    $exception=null,
    $description='performing an operation',
    $return_value=null,
    $output=null;
  
  //Executes a closure and catches any expected exceptions.
  public function __construct($description=null, \Closure $callback, array $arguments = [])
  {
    
    if(is_string($description)){
      $this->description = strtolower(trim($description, ' .!?'));
    }
    
    try{
      ob_start();
      $this->return_value = call_user_func_array($callback, $arguments);
      $this->output = ob_get_contents();
      ob_end_clean();
    }
    
    catch(\exception\Expected $e){
      $this->success = false;
      $this->exception = $e;
      return;
    }
    
    $this->success = true;
    
  }
  
  //Creates a message based on exceptions caught and operation description.
  public function getUserMessage($description=null)
  {
    
    if($this->success){
      $message = '"%s" was successful';
    }
    
    else
    {
    
      switch($this->exception->getExCode())
      {
        
        case EX_AUTHORISATION:
          $message = 'Failed to authorise while %s: %s';
          break;
      
        case EX_VALIDATION:
          $message = 'Failed to validate while %s, because: %s';
          break;
      
        case EX_EMPTYRESULT:
          $message = 'Failed to find database data needed for %s, because: %s';
          break;
      
        default: case EX_EXPECTED: case EX_USER:
          $message = 'Something went wrong while %s, because: %s';
          break;
          
      }
    
    }
    
    return ucfirst(sprintf(
      $message,
      (is_null($description) ? $this->description : strtolower(trim($description, ' .!?'))),
      ($this->exception instanceof \Exception ? ucfirst(strtolower(trim($this->exception->getMessage(), ' .!?'))) : 'No exception')
    )).'.';
  
  }
  
}
