<?php namespace classes;

class SqlConnection extends \PDO
{
  
  public
    $config;
  
  public function __construct($server)
  {
    
    $this->config = $c = tx('Config')->database($server);
    $dsn = "{$this->config->type}:host={$this->config->host};".(!is_null($this->config->port) ? "port={$this->config->port};" : '')."dbname={$this->config->name}";
    parent::__construct($dsn, $this->config->user, $this->config->password);
    $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
    
  }
  
  public function quote($value, $parameter_type = self::PARAM_STR)
  { 
    
    if(is_null($value)){
      return 'NULL';
    }
    
    return parent::quote($value, $parameter_type);
    
  }
  
}
