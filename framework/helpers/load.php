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
      'exceptions, interfaces, traits or classes.', $class
    ));
  }
  
  $file = realpath(dirname(__FILE__).'/../system').'/'.implode('/', $class_array).'.php';
  
  if(!is_file($file)){
    die(sprintf('Failed to auto-load: "%s"', $file));
  }
  
  require_once($file);
  
}

function files($pattern, $flags=0)
{
  $glob = glob($pattern, $flags);
  return (is_array($glob) ? $glob : []);
}

function load_class($file, $class)
{
  
  //Check for class existence.
  if(class_exists($class, false)){
    return true;
  }
  
  //Check for file existence.
  if(!file_exists($file)){
    throw new \exception\FileMissing($file);
  }
  
  //Require the file. Once.
  require_once($file);
  
  //Check for class existence.
  if(!class_exists($class, false)){
    throw new \exception\Programmer('Expecting file "%s" to have class %s.', $file, $class);
  }
  
  //Done.
  return $class;
  
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
