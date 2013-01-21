<?php namespace classes\data;

class EmailWrapper extends StringWrapper
{
  
  //Protected properties.
  protected
    $parsed=[];
  
  //Return the local part of the email address.
  public function getLocal()
  {
    
    //Pre-parse the email address.
    $this->parseEmail();
    
    //Return the wrapped local.
    return new StringWrapper($this->parsed['local']);
    
  }
  
  //Return the domain part of the email address.
  public function getDomain()
  {
    
    //Pre-parse the email address.
    $this->parseEmail();
    
    //Return the wrapped domain.
    return new StringWrapper($this->parsed['domain']);
    
  }
  
  //Parse the email and cache the results.
  public function parseEmail()
  {
    
    //Don't do stuff if it's already cached.
    if(count($this->parsed)){
      return $this;
    }
    
    //"Parse" the email address.
    $parsed = explode('@', $this->value);
    
    //Set the values.
    $this->parsed['local'] = $parsed[0];
    $this->parsed['domain'] = $parsed[1];
    
    //Enable chaining.
    return $this;
    
  }
  
}
