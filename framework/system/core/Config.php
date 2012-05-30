<?php namespace core;

class Config
{
  
  private
    $config = [],
    $database = [];
  
  public
    $paths,
    $urls;
    
  public
    $site;
  
  public function init()
  {
  
    //The path to the configuration folder.
    $path_config = @realpath(@dirname(__FILE__).'/../../config');
    
    //Set constants
    foreach(require("$path_config/constants.php") as $key => $value){
      define(strtoupper($key), $value);
    }
    
    //Set paths.
    $this->paths = new \classes\Configuration('*', require("$path_config/paths.php"));
    
    //Set urls.
    $this->urls = new \classes\Configuration('*', require("$path_config/urls.php"));
    
    //Set database config.
    $this->database = $this->_setMulti(require("$path_config/database.php"));
    
    //Set configuration.
    $this->config = $this->_setMulti(require("$path_config/config.php"));
    
    //Enter a log entry.
    tx('Log')->message($this, 'Config class initialized.');
    
  }
  
  public function __get($key)
  {
    
    return $this->__call($key, [tx('Server')->http_host]);
    
  }
  
  public function __call($key, $arguments)
  {
    
    if(!(isset($this->{$key}) && is_array($this->{$key}))){
      throw new \exception\InvalidArgument('Config variable "%s" is not an array.', $key);
    }
    
    if(count($arguments) < 1){
      throw new \exception\InvalidArgument('Expecting at least one argument. 0 Given.');
    }
    
    if(!array_key_exists($arguments[0], $this->{$key})){
      throw new \exception\NotFound('No configuration settings found for domain: "%s".', $arguments[0]);
    }
    
    return $this->{$key}[$arguments[0]];
    
  }
  
  private function _setMulti(array $arr)
  {
    
    $return = [];
    
    if(array_key_exists('*', $arr)){
      $return['*'] = $defaults = new \classes\Configuration('*', $arr['*']);
      unset($arr['*']);
    }
    
    foreach($arr as $domain => $values){
      $return[$domain] = new \classes\Configuration($domain, $values, $defaults);
    }
    
    if(!array_key_exists(tx('Server')->http_host, $return)){
      $return[tx('Server')->http_host] = new \classes\Configuration(tx('Server')->http_host, [], $defaults);
    }
    
    return $return;
    
  }
  
}
