<?php namespace classes;

class SqlResult
{
  
  protected
    $rows=[];
  
  public function __construct(\mysqli_result $result)
  {
    while($this->rows[] = $result->fetch_assoc());
    array_pop($this->rows);
    $result->free();
  }
  
  public function row($key=null)
  {
    
    if(is_null($key)){
      $row = current($this->rows);
      next($this->rows);
      return $row;
    }
    
    elseif(is_int($key))
    {
      
      if(array_key_exists($key, $this->rows)){
        return $this->rows[$key];
      }
      
      else{
        return false;
      }
      
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