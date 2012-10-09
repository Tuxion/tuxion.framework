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
    
    return $this->_set('limit', $input, 0);
    
  }
  
  //Set the offset.
  public function setOffset($input)
  {
    
    return $this->_set('offset', $input, 1);
    
  }
  
  //Extend the parent functionality.
  public function getString()
  {
    
    return "LIMIT {$this->limit}".(is_null($this->offset) ? '' : " OFFSET {$this->offset}");
    
  }
  
  //Used internally to reuse code.
  private function _set($key, $input, $datakey)
  {
    
    
    //Prepare the input.
    $prepared = $this->builder->prepare($input);
    
    //If the input was a string, store it in the data.
    if($prepared === false)
    {
      
      //It must be numeric.
      if(!is_numeric($input)){
        throw new \exception\InvalidArgument(
          'Expecting $input to be numeric. Non-numeric value (%s) given.', $input
        );
      }
      
      //Set
      $this->{$key} = '?';
      $this->data[$datakey] = $input;
      
    }
    
    //Otherwise use the prepared input.
    else{
      $this->{$key} = $prepared;
    }
    
    //Enable chaining.
    return $this;
    
  }
  
}
