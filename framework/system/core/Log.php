<?php namespace core;

class Log
{

  //Initialize.
  public function init()
  {
    
    //Enter a log entry.
    $this->message(__CLASS__, 'class initialize', 'Log class initializing.');
    
    //Enter a log entry.
    $this->message(__CLASS__, 'class initialize', 'Log class initialized.');
    
  }

  //Log an exception.
  public function error($namespace, $e)
  {
    
    //The log_errors setting.
    $c = tx('Config')->config->log_errors;
    
    //Is error logging even enabled?
    if($c === false){
      return false;
    }
    
    
    //Are we even allowed to log this error?
    if(is_int($c) && !checkbit($e->getExCode(), $c)){
      return false;
    }
    
    //Log it!
    return $this->log(
      $namespace,
      ucfirst(str_replace('\\', ': ', get_class($e))),
      $e->getMessage().' ('.basename($e->getFile()).':'.$e->getLine().')'
    );
    
  }
  
  //Log a system message.
  public function message()
  {
    
    //Are we even allowed to log messages?
    if(tx('Config')->config->log_messages !== true){
      return false;
    }
    
    //Handle arguments.
    $args = func_get_args();
    
    //Is the newline option provided?
    if(is_bool(end($args))){
      $newline = array_pop($args);
    }else{
      $newline = false;
    }
    
    //A message must have been given.
    if(empty($args)){
      throw new \exception\InvalidArgument('Expecting a message. It was not given.');
    }
    
    //Get the message.
    $message = array_pop($args);
    
    //Both key and event given.
    if(count($args) == 2){
      $key = array_shift($args);
      $event = array_shift($args);
    }
    
    //Only a key given.
    elseif(count($args) == 1){
      $key = array_shift($args);
      $event = null;
    }
    
    //None given.
    else{
      $key = null;
      $event = null;
    }
    
    //Make sure all arguments were handled.
    if(!empty($args)){
      throw new \exception\InvalidArgument('Invalid arguments given.');
    }
    
    //Log it!
    return $this->log($key, $event, $message, $newline);
    
  }
  
  //Log something to a file.
  private function log($key=null, $event=null, $message='Empty log entry.', $newline=false)
  {
    
    //Check if logging is enabled.
    if((is_null(tx('Config')->config->logging) ? tx('Config')->config->debug : tx('Config')->config->logging) !== true){
      return false;
    }
    
    //Prepend a newline?
    $n = $newline ? "\n" : '';
    
    //Prepare a date.
    $date = date('Y-m-d H:i:s').' ';
    
    //Prepare the key.
    $key = (is_string($key) ? str_replace(array("\r", "\r\n", "\n", "  "), '', "[$key] ") : '');
    
    //Prepare the event.
    $event = (is_string($event) ? str_replace(array("\r", "\r\n", "\n", "  "), '', "--$event-- ") : '');
    
    //Will we log the message into the server logs?
    if(!tx('Config')->config->log_file){
      return error_log("$key$event$message", 4);
    }
    
    //Will we log the message into a file?
    elseif(is_string(tx('Config')->config->log_file))
    {
      
      $file = realpath( tx('Config')->paths->root . '/' . tx('Config')->config->log_file );
      
      if(!is_file($file)){
        return false;
      }
      
      return error_log("$n$date$key$event$message\n", 3, $file);
      
    }
    
    //Nope.
    return false;
    
  }
  
}
