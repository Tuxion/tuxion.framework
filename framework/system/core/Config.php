<?php namespace core;

class Config
{

  private
    $config,
    $database,
    $path,
    $url;
    
  public
    $site;
  
  public function __construct()
  {
    
    $this->config = Data();
    $this->database = Data();
    $this->path = Data();
    $this->url = Data();
    
  }
  
  public function init($site)
  {
  
    if(empty($site)){
      die('No site-name given.');
    }
    
    $this->site = $site;
    $path_config = @realpath(@dirname(__FILE__).'/../../config');
    
    //Set constants
    foreach(require("$path_config/constants.php") as $key => $value){
      define(strtoupper($key), $value);
    }
    
    //Set paths.
    foreach(require("$path_config/paths.php") as $key => $value){
      $this->path[$key] = $value;
    }
    
    //Set urls.
    foreach(require("$path_config/urls.php") as $key => $value){
      $this->url[$key] = $value;
    }
    
    //Set user config. (and merge with defaults)
    // foreach(tx('Sql')->execute_query('SELECT * FROM #__cms_config WHERE autoload = 1') AS $row){
      // $this->user[$row->key] = $row->value;
    // }
    
  }
  
  public function config()
  {
    
    switch(func_num_args()){
      case 0: return $this->config[$this->site];
      case 1: return $this->config[$this->site][func_get_arg(0)];
      //case 3: tx('Sql')->execute_non_query('UPDATE #__cms_config SET value = \''.mysql_real_escape_string(func_get_arg(1)).'\' WHERE key = \''.mysql_real_escape_string(func_get_arg(1)).'\'');
      case 2: return $this->config[$this->site][func_get_arg(0)]->set(func_get_arg(1));
    }
    
  }
  
  public function database()
  {
    
    switch(func_num_args()){
      case 0: return $this->database[$this->site];
      case 1: return $this->database[$this->site][func_get_arg(0)];
    }
    
  }
  
  public function path()
  {
    
    switch(func_num_args()){
      case 0: return $this->path;
      case 1: return $this->path[func_get_arg(0)];
    }
    
  }
  
  public function url()
  {
    
    switch(func_num_args()){
      case 0: return $this->url;
      case 1: return $this->url[func_get_arg(0)];
    }
    
  }
  
}