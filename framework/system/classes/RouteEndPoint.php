<?php namespace classes;

class RouteEndPoint extends RouteProcessor
{

  //Set the output.
  public function output($data)
  {
    
    //We need materials.
    $this->needsMaterials('to set the output data');
    
    //Set.
    $this->materials->output = tx('Outputting')->standardize($data);
    
    //Enable chaining.
    return $this;
    
  }

}
