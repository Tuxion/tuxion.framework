<?php

//Allows system classes and exceptions to be automatically loaded upon use.
function __autoload($class)
{
  
  if(substr_count($class, '\\') === 0){
    return __autoload('classes\\'.$class);
  }
  
  $class_array = explode('\\', $class);
  $path_system = realpath(dirname(__FILE__).'/../system');
  
  switch($class_array[0]){
    case 'classes': $file = "$path_system/classes/{$class_array[1]}.php"; break;
    case 'exception': $file = "$path_system/exceptions/{$class_array[1]}.php"; break;
    case 'traits': $file = "$path_system/traits/{$class_array[1]}.php"; break;
    default: throw new \exception\Restriction('Failed to autoload "%s"; autoloading is restricted to only exceptions, traits or core classes.', $class);
  }
  
  if(!is_file($file)){
    throw new \exception\FileMissing($file);
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

function files($pattern)
{
  $glob = glob($pattern);
  return (is_array($glob) ? $glob : []);
}

//A shortcut for "core\Loader::load($arg1='Loader'[, $arg-n[, ...]])" and "new \classes\UserFunction([$arg1, ]$arg2)"
function tx()
{
	
  if(func_num_args() == 0 || is_null(func_get_arg(0))){
		return core\Loader::loadClass('Loader');
	}
  
  elseif(func_num_args() == 2 && is_string(func_get_arg(0)) && (func_get_arg(1) instanceof \Closure)){
    return new \classes\UserFunction(func_get_arg(0), func_get_arg(1));
  }
  
  elseif(func_num_args() == 1 && (func_get_arg(0) instanceof \Closure)){
    return new \classes\UserFunction(null, func_get_arg(0));
  }
  
  else{
    return call_user_func_array([tx(), 'load'], func_get_args());
	}
  
}
