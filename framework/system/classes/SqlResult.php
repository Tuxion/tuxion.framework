<?php namespace classes;

class SqlResult extends ArrayObject
{
  
  public function __construct(\mysqli_result $result, $row_model = '')
  {
    
    while($row = $result->fetch_assoc()){
      $this->push(new SqlRow($row));
    }
    
  }
  
}