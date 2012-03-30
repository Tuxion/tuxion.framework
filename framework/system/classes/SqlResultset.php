<?php namespace classes;

class SqlResultset extends ArrayObject
{
  
  public
    $num = 0;
  
  protected
    $connection;
  
  public function __construct(SqlConnection $connection)
  {
    
    $this->connection = $connection;
    $i=0;
    
    while($this->connection->mysqli->more_results())
    {
      $this->connection->mysqli->next_result();
      $result = $this->connection->mysqli->store_result();
      $this->push(new SqlResult($result));
      $i++;
    }
    
    $this->setArrayPermissions(1,0,0);
    $this->num = $i;
    
  }
  
}
