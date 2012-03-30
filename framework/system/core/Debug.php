<?php namespace core;

class Debug
{

  public function init()
  {
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
    trace(get_class($e), $e->getMessage());
    trace($e->getTrace());
  }

}