<?php

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
    var_dump($trace);
  }
  echo "\n</pre>";
  $tracenum++;
}

function typeof($var){
  return tx('Debug')->typeOf($var);
}

function get_object_id($object)
{
  
  if(!is_object($object)){
    throw new \exception\InvalidArgument(
      'Expecting $object to be an object. %s given.',
      ucfirst(typeof($object))
    );
  }
  
  static $object_ids = [];
  
  $hash = spl_object_hash($object);
  
  if(array_key_exists($hash, $object_ids)){
    $id = $object_ids[$hash];
  }
  
  else{
    $id = $object_ids[$hash] = (count($object_ids))+1;
  }
  
  return $id;

}

function get_object_name($object)
{
  
  return get_class($object).'#'.get_object_id($object);
  
}

