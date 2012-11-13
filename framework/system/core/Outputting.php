<?php namespace core;

class Outputting
{
  
  //Private properties.
  private
    $standards=[],
    $templators=[];  
  
  //Load all outputters on initiation.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Outputting class initializing.');
    
    //Load the outputters.
    $this->loadOutputters();
    
    //Enter a log entry.
    tx('Log')->message($this, 'Outputting class initialized.');
    
  }
  
  //Cast the given data into a suiting StandardData container.
  public function standardize($data)
  {
    
    //Data has already been standardized?
    if($data instanceof \classes\BaseStandardData){
      return $data;
    }
    
    //Get the right class.
    if(!($container_class = $this->getStandardFor($data))){
      throw new \exception\NotImplemented('There is no standard for the provided data.');
    }
    
    //Return an instance.
    return new $container_class($data);
    
  }
  
  //Return the class string to the standard data object suitable for the given data.
  public function getStandardFor($data)
  {
    
    //Iterate the StandardData classes and return the first class that accepts the given data.
    foreach($this->standards as $class){
      if($class::accepts($data)){
        return $class;
      }
    }
    
    //Darn.
    return false;
    
  }
  
  //Create a StandardData instance of the given type using the given data.
  public function createStandardInstance($type, $data)
  {
    
    //Create the class name.
    $class = $this->loadStandardClass($type);
    
    //Return the instance.
    return new $class($data);
    
  }
  
  //Returns the class name for the given standard data type.
  public function loadStandardClass($type)
  {
    
    //Create the class string.
    $class = "\\outputting\\$type\\Standard";
    
    //Check if the class exists.
    if(!class_exists($class)){
      throw new \exception\InvalidArgument('Given $type is not an existing standard data type.');
    }
    
    return $class;
    
  }
  
  //Loads all outputters.
  private function loadOutputters()
  {
    
    $directories = files(tx('Config')->paths->outputting.'/*');
    
    foreach($directories as $directory)
    {
      
      $type = substr(strrchr($directory, '/'), 1);
      
      $this->standards[] = load_class("$directory/Standard.php", "\\outputting\\$type\\Standard");
      $this->templators[] = load_class("$directory/Templator.php", "\\outputting\\$type\\Templator");
      
    }
    
  }
  
}
