<?php namespace core;

class Resource
{
  
  //Initialize.
  public function init()
  {
    
    //Create a log entry.
    tx('Log')->message($this, 'Resource class initialized.');
    
  }
  
  //Return a resource locator.
  public function __call($type, $args)
  {
    
    //Delegate.
    return $this->getLocator(ucfirst($type), $args[0]);
    
  }
  
  //Find or create a resource locator of given [type] and identified by [name].
  public function getLocator($type, $name, $location = null, \classes\locators\Base $parent = null)
  {
    
    //Build a class name.
    $class = "\\classes\\locators\\$type";
    
    //Check if this is a valid type.
    if(!class_exists($class)){
      throw new \exception\FileMissing('No resource of type %s exists.', $type);
    }
    
    //Do we have a location? If not; find it by type.
    if(is_null($location) && is_null($parent)){
      $location = $this->findLocationOf($type);
    }
    
    //Create the resource locator.
    $r = new $class($name, $location, $parent);
    
    //Return the resource locator.
    return $r;
    
  }
  
  //Return the system-specified location of a certain resource [type]. NULL If none found.
  public function findLocationOf($type)
  {
    
    //Just a big switch clause.
    switch($type){
      case 'Component': return tx('Config')->paths->components;
      case 'Template': return tx('Config')->paths->templates;
      case 'StandardData': return tx('Config')->paths->outputting;
    }
    
    //None found.
    return null;
    
  }
  
}
