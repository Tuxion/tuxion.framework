<?php namespace core;

class Sql
{
  
  private
    $connections = [];
  
  //Execute a query under the default domain.
  //exe(string $query[, $parameter[, ...]]);
  public function exe()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //At least a query must have been given.
    if(count($args) < 1){
      throw new \exception\InvalidArgument('Expecting at least one argument.');
    }
    
    //Extract the query.
    $query = array_shift($args);
    
    //The parameters.
    $parameters = $args;
    
    //Create and execute a query.
    return $this->query($query, $parameters)->execute();
    
  }
  
  //Returns a query object.
  //query([string $domain=<current_domain>, ]string $query = null[, array $data])
  public function query()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //We're doing stuff based on the number of arguments.
    switch(count($args))
    {
      
      //No arguments given.
      case 0:
        $domain = null;
        $query = null;
        $data = [];
      break;
      
      //Only a query given.
      case 1:
        $domain = null;
        $query = $args[0];
        $data = [];
      break;
      
      //A domain and query, or a query and data.
      case 2:
        if(is_array($args[1])){
          $domain = null;
          $query = $args[0];
          $data = $args[1];
        }else{
          $domain = $args[0];
          $query = $args[1];
          $data = [];
        }
      break;
      
      //Everything given.
      case 3:
        $domain = $args[0];
        $query = $args[1];
        $data = $args[2];
      break;
      
    }
    
    return (new \classes\SqlQuery($this->connection($domain), $query, $data));
    
  }
  
  //Perform a transaction with the given queries.
  //queries([string $domain=<current_domain>, ]array $queries)
  //queries([string $domain=<current_domain>, ]string $queries[, $param[, ...]])
  public function queries()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Validate number of arguments.
    if(count($args) < 1){
      throw new \exception\InvalidArgument('Expecting at least one argument.');
    }
    
    //Check if a domain is given.
    if(count($args) == 2 && substr_count($args[0], '?') === 0){
      $domain = array_shift($args);
    }
    
    //Check again if a domain is given.
    elseif(count($args) > 2 && substr_count($args[1], '?') > 0){
      $domain = array_shift($args);
    }
    
    else{
      $domain = null;
    }
    
    //Check if alternative syntax is used.
    if(is_string($args[0]))
    {
      
      $queries = explode(';', trim(array_shift($args), ';'));
      
      foreach($queries as $k => $query)
      {
        
        $queries[$k] = [$query];
        
        for($i=0;$i<substr_count($query, '?');$i++)
        {
          
          if(count($args) == 0){
            throw new \exception\InputMissing('The amount of data given does not equal the amount of question-marks.');
          }
          
          $queries[$k][] = array_shift($args);
          
        }
        
      }
      
    }
    
    //Otherwise normal syntax must have been used.
    else{
      $queries = array_shift($args);
    }
    
    //Normalize the query-array
    foreach($queries as $k => $query)
    {
      
      if(is_string($query)){
        $queries[$k] = (new \classes\SqlQuery($this->connection($domain)))->setQuery($query);
      }
      
      elseif(is_array($query)){
        $queries[$k] = (new \classes\SqlQuery($this->connection($domain), $query));
      }
      
      elseif(!($query instanceof \classes\SqlQuery)){
        throw new \exception\InvalidArgument('Expecting string, array or an instance of SqlQuery. %s given.', ucfirst(typeof($query)));
      }
      
    }
    
    //Create the SqlMultiQuery object and execute it.
    return (new \classes\SqlMultiQuery($this->connection($domain), $queries))->execute();
    
  }
  
  //Create and cache (or get a cached) SqlConnection
  public function connection($domain=null)
  {
    
    if(is_null($domain)){
      $domain = tx('Server')->http_host;
    }
    
    if(array_key_exists($domain, $this->connections)){
      return $this->connections[$domain];
    }
    
    $this->connections[$domain] = $r = new \classes\SqlConnection($domain);
    
    return $r;
    
  }
  
}
