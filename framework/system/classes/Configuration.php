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
  
  public function __construct($domain, array $values = array(), Configuration $defaults = null, array $cache = [])
  {
    
    $this->domain = $domain;
    $this->values = $values;
    $this->cache = $cache;
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
    if($this->caching && array_key_exists($key, $this->cache) && $this->cache[$key] !== null){
      return $this->cache[$key];
    }
    
    //Check if we can get a value from the database.
    elseif($this->dbo)
    {
      
      $result = tx('Sql')->queries($this->domain, [["SELECT value FROM `{$this->dbo['t']}` WHERE key = ?s", $key]])->result(0);
      
      if($result->hasRows())
      {
        
        if($this->caching){
        
        }
        
        else{
          return $result->row(0)->value;
        }
        
      }
      
      else{
        $this->cache[$key] = null;
      }
      
    }
    
    else{
      return $this->_get($key);
    }
    
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