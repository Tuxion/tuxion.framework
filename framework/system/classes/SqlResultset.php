<?php namespace classes;

class SqlResultset
{
  
  public
    $num = 0;
  
  protected
    $connection,
    $results=[];
  
  public function __construct(SqlConnection $connection)
  {
    
    $this->connection = $connection;
    $i=0;
    
    while($this->connection->mysqli->more_results())
    {
      $this->connection->mysqli->next_result();
      $result = $this->connection->mysqli->store_result();
      $this->results[] = new SqlResult($result);
      $i++;  
    }
    
    
    $this->num = $i;
    
  }
  
  public function result($key)
  {
    
    if(is_null($key)){
      $row = current($this->results);
      next($this->results);
      return $row;
    }
    
    elseif(is_int($key)){
      return $this->results[$key];
    }
    
    else{
      throw new \exception\InvalidArgument('Expecting $key to be null or integer. %s given.', ucfirst(typeof($key)));
    }
    
  }
  
  public function results()
  {
    return $this->results;
  }

}
