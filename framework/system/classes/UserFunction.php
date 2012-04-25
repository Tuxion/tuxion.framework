<?php namespace classes;

class UserFunction
{
  
  //Trait includes.
  use \traits\Successable;
  
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
