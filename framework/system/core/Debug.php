<?php namespace core;

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
    
    echo '<h1>'.get_class($e).'</h1><h3>'.$e->getMessage().'</h3>';
    echo $this->printTrace($e->getTrace());
    
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
    
    $args = function($entry)
    {
      
      $args = [];
      
      foreach($entry['args'] as $arg)
      {
        
        switch(gettype($arg))
        {
          
          case 'array':
            $args[] = 'array('.count($arg).')';
            break;
            
          case 'string':
            $args[] = "'$arg'";
            break;
          
          case 'object':
            if($arg instanceof \Closure){
              $args[] = ('{closure}');
            }else{
              $args[] = ('object('.get_object_name($arg).')');
            }
            break;
            
          default:
            $args[] = $arg;
          
        }
        
      }
      
      
      return '('.implode(', ', $args).')';
      
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
        $out .= '<b title="'.@$entry['file'].'">'.basename(@$entry['file']).'</b>';
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

}
