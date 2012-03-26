<?php namespace classes;

class SqlConnection
{
  
  public
    $config,
    $mysqli;
  
  public function __construct($server)
  {
    
    $this->config = $c = tx('Config')->database($server);
    $this->mysqli = new \mysqli($c->host, $c->user, $c->password, $c->name);
    
  }
  
  public function __destruct()
  {
    $this->mysqli->close();
  }
  
}