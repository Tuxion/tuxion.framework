<?php namespace classes\locators;

class Template extends Base
{
  
  //Return a location string based on which parent is used.
  protected function getLocationByParent(Base $parent)
  {
    
    switch(wrap($parent)->baseclass()->get()){
      case 'Component': return 'templates';
    }
    
    return null;
    
  }
  
}
