<?php namespace classes\route;

use \classes\locators\Base as BaseLocator;

class ControllerContext
{
  
  //Private properties.
  private
    $locator,
    $filename,
    $root_path;
  
  //Set properties.
  public function __construct(BaseLocator $locator, $filename, $root_path)
  {
    
    //We need a Component or a System locator for the Controller to work with.
    if($locator->notAmongst('Component', 'System')){
      throw new \exception\InvalidArgument(
        'Expecting a Component or a System locator. %s given.', typeof($locator)
      );
    }
    
    //Set.
    $this->locator = $locator;
    $this->filename = $filename;
    $this->root_path = $root_path;
    
  }
  
  //Return the locator.
  public function getLocator()
  {
    
    return $this->locator;
    
  }
  
  //Return the filename.
  public function getFilename()
  {
    
    return $this->filename;
    
  }
  
  //Returns the root_path.
  public function getRootPath()
  {
    
    return $this->root_path;
    
  }
  
}
