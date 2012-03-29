<?php namespace classes;

class SqlMultiQuery
{
  
  private
    $prepared = false,
    $connection = null,
    $query,
    $queries = [];
  
  public function __construct()
  {
    
    $args = func_get_args();
    $queries = [];
    
    if(count($args) > 0 && $args[0] instanceof SqlConnection){
      $this->connection = array_shift($args);
    }
    
    if(count($args) > 0 && is_array($args[0])){
      $queries = array_shift($args);
    }
    
    foreach($queries as $query){
      $this->addQuery($query);
    }
    
  }
  
  public function addQuery(SqlQuery $query)
  {
    
    $this->queries[] = $query;
    $this->prepared = false;
    
    return $this;
    
  }
  
  public function getQuery(SqlConnection $conn = null)
  {
    
    $conn = is_null($conn) ? $this->connection : $conn;
    
    if(is_null($conn)){
      throw new \exception\InputMissing('No default connection set.');
    }
    
    if($this->prepared){
      return $this->query;
    }
    
    $querystrings = [];
    foreach($this->queries as $query){
      array_push($querystrings, $query->getQuery($conn));
    }
    
    return implode(';', $querystrings);
    
  }
  
  public function execute(SqlConnection $conn = null)
  {
    
    $conn = is_null($conn) ? $this->connection : $conn;
    
    if(is_null($conn)){
      throw new \exception\InputMissing('No default connection set.');
    }
    
    $query = $this->getQuery($conn);
    $conn->mysqli->multi_query($query);
    
    return new SqlResultset($conn);
    
  }
  
  public function getQueries()
  {
    return $this->queries;
  }

}