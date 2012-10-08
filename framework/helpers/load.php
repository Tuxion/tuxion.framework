<?php

//Allows system classes and exceptions to be automatically loaded upon use.
function __autoload($class)
{
  
  if(substr_count($class, '\\') === 0){
    return __autoload('classes\\'.$class);
  }
  
  $class_array = explode('\\', $class);
  
  if(!in_array($class_array[0], ['classes', 'exception', 'traits', 'interfaces'])){
    die(sprintf(
      'Failed to auto-load "%s"; auto-loading is restricted to only: '.
      'exceptions, interfaces, traits or core classes.', $class
    ));
  }
  
  $file = realpath(dirname(__FILE__).'/../system').'/'.implode('/', $class_array).'.php';
  
  if(!is_file($file)){
    die(sprintf('Failed to auto-load: "%s"', $file));
  }
  
  require_once($file);
  
}

function load_html($___path, array $___data=[], $___once=false)
{

  static $file_checks = [];
  
  if(!in_array($___path, $file_checks)){
    if(is_file($___path)){
      $file_checks[] = $___path;
    }else{
      throw new \exception\FileMissing("Could not load contents of <b>%s</b>. It is not a file.", $___path);
    }
  }
  
  elseif($___once===true){
    return '';
  }
  
  extract($___data);
  unset($___data);

  ob_start();
    require($___path);
    $contents = ob_get_contents();
  ob_end_clean();
  
  return $contents;

}

function files($pattern, $flags=0)
{
  $glob = glob($pattern, $flags);
  return (is_array($glob) ? $glob : []);
}

//A shortcut for "core\Loader::load($arg1='Loader'[, $arg-n[, ...]])"
function tx()
{
	
  if(func_num_args() == 0 || is_null(func_get_arg(0))){
		return core\Loader::loadClass('Loader');
	}
  
  else{
    return call_user_func_array([tx(), 'load'], func_get_args());
	}
  
}
