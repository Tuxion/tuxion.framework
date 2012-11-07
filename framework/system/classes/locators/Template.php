<?php namespace classes\locators;

class Template extends Base
{
  
  //Return a location string based on which parent is used.
  protected function getLocationByParent(Base $parent)
  {
    
    switch(baseclass(get_class($parent))){
      case 'Component': return 'templates';
    }
    
    return null;
    
  }
  
}
