<?php namespace classes;

class Configuration
{
  
  private
    $defaults,
    $values = [],
    $database_options = false;
  
  public function __construct(array $values = array(), Configuration $defaults = null, $database_options = false)
  {
    
    $this->values = $values;
    $this->defaults = (is_null($defaults) ? false : $defaults);
    $this->database_options = $database_options;
    
  }
  
  public function __get($key)
  {
    
    if($this->database_options){
      
      $dbo = $this->database_options;
      $table = (is_array($dbo) && array_key_exists('table', $dbo) ? $dbo['table'] : '#system_config');
      $key = (is_array($dbo) && array_key_exists('key', $dbo) ? $dbo['key'] : 'key');
      $value = (is_array($dbo) && array_key_exists('value', $dbo) ? $dbo['value'] : 'value');
      
      //EXECUTE QUERY!!!!!! CACHE RESULTS!!!!! RAAAARRRGGHHH!!!!! GRUNT ALL THAT!!!!!!! FOR GREAT METAL!!!!!!!!
      
    }
    
    elseif(array_key_exists($key, $this->values)){
      return $this->values[$key];
    }
    
    elseif($this->defaults){
      return $this->defaults->__get($key);
    }
    
    else{
      return null;
    }
    
  }
  
}