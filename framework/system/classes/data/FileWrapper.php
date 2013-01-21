<?php namespace classes\data;

class FileWrapper extends StringWrapper
{
  
  //Private properties.
  private
    $is_part=false;
  
  //Set the value and deal with ".part".
  public function __construct($value)
  {
    
    //Deal with ".part".
    if(substr_count($value, '.part') > 0){
      $this->is_part = true;
      $value = str_replace('.part', '', $value);
    }
    
    //Set the value.
    $this->value = $value;
    
  }
  
  //Get the true file extension.
  public function getExt()
  {
    
    //Return the default extension?
    if(!$this->hasExt()){
      return new StringWrapper('html');
    }
    
    //Return the extension.
    return (new StringWrapper(strrchr($this->value, '.')))->trim(LEFT, '.');
    
  }
  
  //Return the name without any extensions.
  public function getName()
  {
    
    return new StringWrapper($this->hasExt() ? strchr($this->value, '.', true) : $this->value);
    
  }
  
  //Return true if the file has an extension.
  public function hasExt()
  {
    
    return (substr_count($this->value, '.') > 0);
    
  }
  
  //Return the mime-type of the file extension.
  public function getMime()
  {
    
    //Get the mime-type.
    $mime = tx('Mime')->getMime($this->getExt());
    
    //No mime found?
    if($mime === false){
      throw new \exception\NotFound('No mime-type found for extension: "%s"', $this->getExt());
    }
    
    //Return the mime-type.
    return new StringWrapper($mime);
    
  }
  
  //Return true if the file has a .part extension.
  public function isPart()
  {
    
    return $this->is_part;
    
  }
  
}
