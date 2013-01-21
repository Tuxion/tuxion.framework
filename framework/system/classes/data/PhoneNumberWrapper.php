<?php namespace classes\data;

class PhoneNumberWrapper extends StringWrapper
{
  
  //Cast the phone number to an actual number.
  public function toInt()
  {
    
    return new NumberWrapper(intval($this->value));
    
  }
  
  //Return a clean phone number.
  public function clean()
  {
    
    return new self(preg_replace('~[^\d]+~', '', $this->value));
    
  }
  
}
