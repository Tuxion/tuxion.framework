<?php namespace exception;

class Exception extends \Exception
{

  protected $prev; //Thanks PHP...
  
  public function __construct()
  {
    
    //Get arguments.
    $args = func_get_args();
    
    //Place array arguments in an unordered list.
    foreach($args as $k => $arg){
      if(is_array($arg)){
        $args[$k] = ul($arg);
      }
    }
    
    //Do we even have a message?
    if(empty($args)){
      $message = 'Error! Error! Abort operations! Abandon ship! Get to tha choppa!';
    }
    
    //Yep, we do.
    else{
      $message = call_user_func_array('sprintf', $args);
    }
    
    //construct the parent Exception with the message we created.
    parent::__construct($message);
    
    //Should we log this exception?
    if(tx('Config')->config->log_exception_caught){
      tx('Log')->error(__CLASS__, $this);
    }
    
  }
  
  public function setPrev(Exception $previous)
  {
    $this->prev = $previous;
  }
  
  public function getPrev()
  {
    return $this->prev;
  }
  
}
