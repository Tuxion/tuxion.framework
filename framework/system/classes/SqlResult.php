<?php namespace classes;

class SqlResult extends ArrayObject
{
  
  public function __construct(\PDOStatement $statement, $row_model = '')
  {
    
    foreach($statement->fetchAll(\PDO::FETCH_ASSOC) as $row){
      $this->push(new SqlRow($row));
    }
    
    $this->setArrayPermissions(1,0,0);
    
  }
  
}