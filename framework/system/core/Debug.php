<?php namespace core;

class Debug
{

  public function __construct()
  {
    
    //Set error variables.
    error_reporting(E_ALL | E_STRICT);
    set_error_handler([$this, 'errorHandler']);
    set_exception_handler([$this, 'exceptionHandler']);
    
  }
  
  public function errorHandler($errno, $errstr='', $errfile='', $errline='', $context=array())
  {
  
    if((error_reporting() & $errno) == 0){
      return;
    }
    
    throw new \exception\Error($errno, $errstr, $errfile, $errline, $context);

  }
  
  public function exceptionHandler($e)
  {
    
    //Log it if it hasn't already logged itself.
    if(!tx('Config')->config->log_error_caught){
      tx('Log')->error(__CLASS__, $e);
    }
    
    trace(get_class($e), $e->getMessage());
    trace($e->getTrace());
    exit;
    
  }
  
  public function typeOf($var)
  {
    return (is_object($var) ? sprintf('object(%s)', get_class($var)) : gettype($var));
  }

}
