<?php namespace classes;

class SqlResultset extends ArrayObject
{
  
  public function __construct(\PDOStatement $statement)
  {
    
    $i = 1;
    
    try{
    
      do{
        $i++;
        $this->push(new SqlResult($statement));
      }
      
      while($statement->nextRowset());
      
    }
    
    catch(\PDOException $e){
      throw new \exception\Sql($e->getMessage().' in query %s.', $i);
    }
    
    catch(\exception\Sql $e){
      throw new \exception\Sql($e->getMessage().' in query %s.', $i);
    }
    
    $this->setArrayPermissions(1,0,0);
    
  }
  
  private function exceptionHandler($e){
    throw $e;
  }
  
}
