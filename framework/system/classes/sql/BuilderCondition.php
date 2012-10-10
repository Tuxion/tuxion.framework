<?php namespace classes\sql;

class BuilderCondition
{
  
  //Private properties.
  private
    $builder,
    $data=[],
    $conditions='';
  
  //The constructor adds the first condition.
  public function __construct(Builder $builder, array $args)
  {
    
    $this->builder = $builder;
    call_user_func_array([$this, 'addCondition'], array_merge([null], $args));
    
  }
  
  //We use method overloading in order to bypass the keyword restriction on "and" and "or".
  public function __call($key, $args)
  {
    
    return call_user_func_array([$this, "addCondition"], array_merge([strtoupper($key)], $args));
    
  }
  
  //Add a condition.
  private function addCondition()
  {
    
    //Handle arguments.
    $args = func_get_args();

    //Get the connector.
    $connector = array_shift($args);
    $connector = (is_null($connector) ? '' : " $connector ");
    
    //Check if the first argument is a subset of conditions.
    if($args[0] instanceof self){
      $this->conditions .= "$connector(".$this->builder->prepare($args[0]).')';
      $this->data = array_merge($this->data, $args[0]->getData());
      return $this;
    }
    
    //We do stuff based on the amount of arguments.
    switch(count($args))
    {
      
      //One argument given. We check if it's not null.
      case 1:
        $key = $args[0];
        $operator = 'NOT';
        $value = 'NULL';
      break;
      
      //Two arguments given. We choose an operator based on what the arguments are.
      case 2:
        
        //Get the key and value.
        $key = $args[0];
        $value = $args[1];
        
        //If the value is an array, we use an IN operator.
        if(is_array($value)){
          $operator = 'IN';
        }
        
        //Anything else uses the default equals-to operator.
        else{
          $operator = '=';
        }
        
      break;
      
      //Everything given. We'll normalize later.
      case 3:
        $key = $args[0];
        $operator = $args[1];
        $value = $args[2];
      break;
      
    }
      
    //Normalize the operator when NULL is used as value.
    if($value == 'NULL'){
      $operators = ['=' => 'IS', '!=' => 'IS NOT', '<>' => 'IS NOT'];
    }
    
    //Normalize the operator when an array is used as value.
    elseif(is_array($value))
    {
      
      $operators = ['=' => 'IN', '!=' => 'NOT IN', '<>' => 'NOT IN'];
      
      #TODO: Convert arrays:
      // //Convert arrays to a comma-separated list.
      // if(is_array($input)){
        
      //   //Return the whole thing in brackets.
      //   return '('.
        
      //   //Wrap.
      //   Data($input)
        
      //   //Prepare all individual values.
      //   ->map(function($value){
      //     return $this->prepare($value->get());
      //   })
        
      //   //Join the values together.
      //   ->join(', ')
        
      //   //Add the closing bracket.
      //   .')';
        
      // }
      
    }
    
    //Normalize when a function is used as value.
    elseif($value instanceof BuilderFunction){
      $operator = null;
    }
    
    //Nothing.
    else{
      $operators = [];
    }
    
    //Normalize.
    $operator = (array_key_exists($operator, $operators) ? $operators[$operator] : $operator);
    
    //Prepare variables.
    $operator = (is_null($operator) ? ' ' : " $operator ");
    $prepared_key = $this->builder->prepare($key);
    $prepared_value = $this->builder->prepare($value);
    
    //Check if we need to add the key to our data.
    if($prepared_key === false){
      $prepared_key = '?';
      $this->data[] = $key;
    }
    
    //Check if we need to add the value to our data.
    if($prepared_value === false){
      $prepared_value = '?';
      $this->data[] = $value;
    }
    
    //Create the piece of string.
    $this->conditions .= "$connector$prepared_key$operator$prepared_value";
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return the conditions transformed into a query-string.
  public function getString()
  {
    
    return $this->conditions;
    
  }
  
  //Return the data needed to fill the query-string up.
  public function getData()
  {
    
    return $this->data;
    
  }

}
