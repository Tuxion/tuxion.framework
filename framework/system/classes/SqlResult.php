<?php namespace classes;

class SqlResult
{
  
  protected
    $rows=[];
  
  public function __construct(\mysqli_result $result)
  {
    
    $rows = [];
    while($rows[] = $result->fetch_assoc());
    array_pop($rows);
    $this->rows = $rows;
    $result->free();
    
  }
  
  public function row($key=null)
  {
    
    if(is_null($key)){
      $row = current($this->rows);
      next($this->rows);
      return $row;
    }
    
    elseif(is_int($key)){
      return $this->rows[$key];
    }
    
    else{
      throw new \exception\InvalidArgument('Expecting $key to be null or integer. %s given.', ucfirst(typeof($key)));
    }
    
  }

  public function rows()
  {
    return $this->rows;
  }
  
}