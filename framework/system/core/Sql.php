<?php namespace core;

class Sql
{
  
  private
    $connections = [];
  
  //Execute a query.
  public function query()
  {
    return (new \classes\SqlQuery($this->connection(), func_get_args()))->execute();
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
            throw new \exception\InputMissing('The amount of data given does not equal the amount of questionmarks.');
          }
          
          $queries[$k][] = array_shift($args);
          
        }
        
      }
      
    }
    
    //Otherwise normal sysntax must have been used.
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
      $domain = tx('Data')->server->HTTP_HOST->get();
    }
    
    if(array_key_exists($domain, $this->connections)){
      return $this->connections[$domain];
    }
    
    $this->connections[$domain] = $r = new \classes\SqlConnection($domain);
    
    return $r;
    
  }
  
}