<?php namespace classes;

class Configuration
{
  
  private
    $domain,
    $defaults,
    $values = [],
    $cache = [],
    $caching = true,
    $dbo = false;
  
  public function __construct($domain, array $values = array(), Configuration $defaults = null)
  {
    
    $this->domain = $domain;
    $this->values = $values;
    $this->defaults = (is_null($defaults) ? false : $defaults);
    
    if($this->_get('config_table', false)){
      $this->dbo = [
        't' => $this->_get('config_table', '#system_config'),
        'k' => $this->_get('config_key', 'key'),
        'v' => $this->_get('config_value', 'value')
      ];
    }
    
  }
  
  public function __get($key)
  {
    
    //Check if the value is already in the cache.
    if($this->caching && array_key_exists($key, $this->cache)){
      return $this->cache[$key];
    }
    
    //Check if we can get a value from the database.
    elseif($this->dbo)
    {
      
      $result = tx('Sql')->query($this->domain, "SELECT value FROM `{$this->dbo['t']}` WHERE `key` = ?s", [$key])->execute();
      
      if($result->num() > 0)
      {
        
        if($this->caching){
          $this->cache[$key] = $r = $result->idx(0)->value;
        }
        
        else{
          $r = $result->idx(0)->value;
        }
        
        return $r;
        
      }
      
    }
    
    $r = $this->_get($key);
    
    if($this->caching){
      $this->cache[$key] = $r;
    }
    
    return $r;
    
  }
  
  public function enableCache()
  {
    
    $this->caching = true;
    //$this->cache = [];
    
    return $this;
    
  }
  
  public function disableCache()
  {
    
    $this->caching = false;
    
    return $this;
    
  }
  
  private function _get($key, $default = null)
  {
    
    if(array_key_exists($key, $this->values)){
      return $this->values[$key];
    }
    
    elseif($this->defaults){
      return $this->defaults->__get($key);
    }
    
    else{
      return $default;
    }
    
  }
  
}
