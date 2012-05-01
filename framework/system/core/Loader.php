<?php namespace core;

class Loader
{
	
  private static $objects = array();
	
  //Loads the given class_name from system/core.
  public static function loadClass($class_name, array $args=array())
  {
    
    //Validate arguments.
    if(empty($class_name)){
      throw new \exception\InvalidArgument('Class name was empty');
    }
    
    //See if we need to load the class.
    if( ! array_key_exists($class_name, self::$objects) )
    {
      
      //Define namespaced class and class file.
      $class = "\\core\\$class_name";
      $file = dirname(__FILE__)."/$class_name.php";
      
      //Validate class-file presence.
			if( ! is_file($file) ){
				die(sprintf('Core class "%s" was not found.', $file));
			}
      
      //Require the file.
			require_once($file);
      
      //Validate class presence.
      if( ! class_exists($class) ){
        die(sprintf('The class "%s" is not defined in its file.', $class));
      }
      
      //Create a single instance and store it in self::$objects
      $instance = new $class;
      self::$objects[$class_name] = $instance;
      
      //Call the init function?
      if(method_exists($instance, 'init')){
        call_user_func_array(array($instance, 'init'), $args);
      }
      
    }
    
    //Return the instance.
    return self::$objects[$class_name];
    
  }
  
  //Initialize.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Loader class initializing.');

    //Enter a log entry.
    tx('Log')->message($this, 'Loader class initialized.');
    
  }
	
  //A somewhat higher-level non-static version of static::load().
	public function load()
	{
		
    $args = func_get_args();
    $class_name = array_shift($args);
    
    return self::loadClass($class_name, $args);
    
	}
	
}
