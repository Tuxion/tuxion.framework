<?php namespace classes\locators;

class Component extends BaseDatabase
{
  
  //Public properties.
  public
    $table_name='#system_outputting_types';
    
  //Return a location string based on which parent is used.
  public function getLocationByParent(Base $parent)
  {
    
    return null;
    
  }
  
}
