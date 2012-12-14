<?php namespace core;

use \classes\data\BaseData;

class Debug
{

  public function __construct()
  {
    
    //Set error variables.
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 'on');
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
    if(!tx('Config')->config->log_exception_caught){
      tx('Log')->error(__CLASS__, $e);
    }
    
    //Create the message.
    $msg = sprintf(
      '%s: %s'.(tx('Config')->config->debug ? ' (%s @ %s)' : ''),
      wrap($e)->baseclass()->get(),
      $e->getMessage(),
      substr($e->getFile(), strlen(tx('Config')->paths->root)+1),
      $e->getLine()
    );
    
    #TEMP: Commented until better exception handling is implemented.
    //Uncaught exceptions. We can't do much with them.
    // set_status_header($this->getExceptionResponseCode($e), $msg);
    
    // //Give the output in HTML.
    // header('Content-type: text/html; charset=UTF-8');
    
    //Output the message.
    echo $msg;
    echo BR.BR;
    echo $this->printTrace($e->getTrace());
    
    //We're dead.
    exit;
    
  }
  
  public function printTrace(array $trace)
  {
    
    $func = function($entry)
    {
      
      $ret = '';
      
      if(substr_count($entry['function'], '{closure}') > 0)
      {
        
        $ret .= substr($entry['function'], 0, -1);
        
        if(array_key_exists('class', $entry)){
          $ret .= ' with context "'.($entry['type'] == '->' ? "object({$entry['class']})" : $entry['class']).'"';
        }

        $ret .= '}';
        
      }
      
      else
      {
        
        if(array_key_exists('class', $entry)){
          $ret .= ($entry['type'] == '->' ? "object({$entry['class']})" : $entry['class']).$entry['type'];
        }
        
        $ret .= $entry['function'];
        
      }
      
      return $ret;
      
    };
    
    $args = function($entry)use(&$args)
    {
      
      $arr = [];
      
      foreach($entry['args'] as $arg)
      {
        
        switch(gettype($arg))
        {
          
          case 'array':
            $arr[] = 'array('.count($arg).')';
            break;
            
          case 'string':
            $arr[] = "'$arg'";
            break;
          
          case 'object':
            if($arg instanceof \Closure){
              $arr[] = ('{closure}');
            }
            elseif($arg instanceof BaseData){
              $arr[] = ('data'.$args(['args' => [$arg->get()]]));
            }
            else{
              $arr[] = ('object('.wrap($arg)->name().')');
            }
            break;
          
          case 'NULL':
            $arr[] = 'NULL';
            break;
          
          case 'boolean':
            $arr[] = ($arg ? 'true' : 'false');
            break;
          
          default:
            $arr[] = $arg;
          
        }
        
      }
      
      
      return '('.implode(', ', $arr).')';
      
    };
    
    $trace = array_reverse($trace);
    $i=0;
    $out = '';
    
    while(array_key_exists($i, $trace))
    {
      
      $entry = $trace[$i];
      
      if(array_key_exists('class', $entry) && $entry['function'] == '__call'){
        $i++;
        continue;
      }
      
      if(!array_key_exists('file', $entry)){
        $out .= '<b>[internal code]</b>';
      }else{
        $out .= '<b title="'.@substr(@$entry['file'], strlen(tx('Config')->paths->root)+1).'">'.basename(@$entry['file']).'</b>';
        $out .= '@<i>'.@$entry['line'].'</i>';
      }
      
      $out .= "\t:\t<code>";
      
      //Combine call_user_funcs with the next entry.
      if($entry['function'] == 'call_user_func' || $entry['function'] == 'call_user_func_array'){
        
        $out .= $func($entry).'( '.$func($trace[$i+1]).''.$args($trace[$i+1]).' )';
        $i++;
        
      }
      
      //Normal function output.
      else{
        $out .= $func($entry).$args($entry);
      }
      
      $out .= '</code>';
      $out .= "<br />\n";
      
      $i++;
      
    }
    
    return $out;
    
  }
  
  public function typeOf($var)
  {
    
    return (is_object($var) ? sprintf('object(%s)', get_class($var)) : gettype($var));
    
  }
  
  //Return the response code associated with this exception.
  public function getExceptionResponseCode(\Exception $e)
  {
    
    if($e instanceof \exception\BadRequest){
      return 400;
    }
    
    if($e instanceof \exception\Unauthorized){
      return 401;
    }
    
    if($e instanceof \exception\Forbidden){
      return 403;
    }
    
    if($e instanceof \exception\NotFound){
      return 404;
    }
    
    
    if($e instanceof \exception\NotImplemented){
      return 501;
    }
    
    return 500;
    
  }
  
}
