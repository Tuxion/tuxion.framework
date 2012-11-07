<?php namespace classes;

class ControllerContext
{
  
  //Private properties.
  private
    $locator,
    $filename;
  
  //Set properties.
  public function __construct(locators\Base $locator, $filename)
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
  
}
