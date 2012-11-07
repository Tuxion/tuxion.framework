<?php namespace exception;

class Error extends \ErrorException
{

  protected $context;
  
  public function __construct($errno, $errstr, $errfile, $errline, $context)
  {
    
    $errnos = array(
      E_ERROR             => 'ERROR',
      E_WARNING           => 'WARNING',
      E_PARSE             => 'PARSING ERROR',
      E_NOTICE            => 'NOTICE',
      E_CORE_ERROR        => 'CORE ERROR',
      E_CORE_WARNING      => 'CORE WARNING',
      E_COMPILE_ERROR     => 'COMPILE ERROR',
      E_COMPILE_WARNING   => 'COMPILE WARNING',
      E_USER_ERROR        => 'USER ERROR',
      E_USER_WARNING      => 'USER WARNING',
      E_USER_NOTICE       => 'USER NOTICE',
      E_STRICT            => 'STRICT NOTICE',
      E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR'
    );
    
    $message = "{$errnos[$errno]}: <b>$errstr</b>";
    
    $this->context = $context;
    
    parent::__construct($message, $errno, 1, $errfile, $errline);
    
    if(tx('Config')->config->log_exception_caught){
      tx('Log')->error(__CLASS__, $this);
    }
    
  }
  
  public function getContext()
  {
    return $this->context;
  }
  
}
