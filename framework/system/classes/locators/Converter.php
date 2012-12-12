<?php namespace classes\locators;

class Template extends BaseDatabase
{
  
  //Public properties.
  public
    $table_name='#system_outputting_converters';
  
  //Return a location string based on which parent is used.
  protected function getLocationByParent(Base $parent)
  {
    
    switch(wrap($parent)->baseclass()->get()){
      case 'StandardData': return 'converters';
    }
    
    return null;
    
  }
  
}
