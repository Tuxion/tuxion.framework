<?php namespace classes\sql\clauses;

class Limit extends BaseClause
{
  
  //Private properties.
  private
    $limit=1,
    $offset=null;
  
  //Set the limit.
  public function setLimit($input)
  {
    
    return $this->limit = $this->prepare($input);
    
  }
  
  //Set the offset.
  public function setOffset($input)
  {
    
    return $this->offset = $this->prepare($input);
    
  }
  
  //Extend the parent functionality.
  public function getString()
  {
    
    return "LIMIT {$this->limit}".(is_null($this->offset) ? '' : " OFFSET {$this->offset}");
    
  }
  
  //Extend the prepare method to check if input is numeric.
  public function prepare($input, &$data = null)
  {
    
    //Prepare.
    $prepared = parent::prepare($input, $data);
    
    //Check for data.
    if(empty($data)){
      return $prepared;
    }
    
    //We want a single numeric value.
    if(count($data) > 1 || !is_numeric($data[0])){
      throw new \exception\InvalidArgument(
        'Expecting a single numeric value. %s given.', implode('" and "', $data)
      );
    }
    
    //OK now.
    return $prepared;
    
  }
  
}
