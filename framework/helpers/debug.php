<?php

function error_handler($errno, $errstr='', $errfile='', $errline='', $context=array()){
  
  if((error_reporting() & $errno) == 0){
    return;
  }
  
  throw new \exception\Error($errno, $errstr, $errfile, $errline, $context);
  
}

function trace(){
  static $tracenum = 1;
  $trace = debug_backtrace(false);
  echo "<pre>\n<b style=\"color:red\">trace(".func_num_args().") #$tracenum called in <span style=\"cursor:help\" title=\"".$trace[0]['file']."\">".basename($trace[0]['file'], '.php')."</span> @ {$trace[0]['line']}:</b>\n";
  if(func_num_args() > 1){
    $i = 1;
    foreach(func_get_args() as $arg){
      echo "\n&raquo; Argument $i:\n";
      var_dump($arg);
      $i++;
    }
    echo "<b>\neof: trace #$tracenum</b>";
  }elseif(func_num_args() == 1){
    var_dump(func_get_arg(0));
  }else{
    echo tx('Error')->callstack();
  }
  echo "\n</pre>";
  $tracenum++;
}