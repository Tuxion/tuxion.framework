<?php namespace classes\sql;

class Query
{
  
  public
    $connection=null;
    
  private
    $prepared=false,
    $query,
    $is_non_query=false,
    $datatypes=[],
    $data=[],
    $creator=null;
  
  //Initialize the Query instance, optionally passing a default connection and query information.
  public function __construct(
    Connection $connection = null,
    $query_string = null,
    array $data = [],
    $non_query = false
  ){
    
    //A default connection passed?
    if(!is_null($connection)){
      $this->connection = $connection;
    }
    
    //A query?
    if(is_string($query_string)){
      $this->setQuery($query_string);
    }
    
    $this->setData($data);
    $this->is_non_query = (bool) $non_query;
      
  }
  
  //Set the query.
  public function setQuery($query)
  {
    
    //Can not redeclare.
    if(!is_null($this->query)){
      throw new \exception\Restriction('Can not redeclare query.');
    }
    
    //Validate stringlyness of argument.
    if(!is_string($query)){
      throw new \exception\InvalidArgument(
        'Expecting $query to be string. %s given.',
        ucfirst(typeof($query))
      );
    }
    
    //Warn the programmer about multiqueries.
    if(substr_count($query, ';') > 0){
      throw new \exception\InvalidArgument(
        'Your query contains the ";"-character. '.
        'Note that Query does not execute multi-queries. Use MultiQuery for that.'
      );
    }
    
    //Types.
    $types = [];
    
    //Find declared datatypes.
    $query = preg_replace_callback('~\?[idsb]?~', function($matches)use(&$types){
      
      $type = $matches[0];
      
      if(strlen($type) == 2){
        $type = substr($type, 1, 1);
      }
      
      $types[] = $type;
      
      return '?';
      
    }, $query);
    
    //Set datatypes.
    foreach($types as $key => $type){
      $this->datatypes[$key] = $type;
    }
    
    //Set other stuff.
    $this->query = $query;
    $this->prepared = false;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Set the data.
  public function setData(array $data)
  {
    
    $this->data = array_values($data);
    $this->prepared = false;
    
    return $this;
    
  }
  
  //Set the creator to use when this query executes.
  public function setCreator(\Closure $creator)
  {
    
    $this->creator = $creator;
    
  }
  
  //What do you think?
  public function isPrepared()
  {
    return $this->prepared;
  }
  
  //Get the query with the data inserted into it.
  public function getQuery(Connection $conn = null)
  {
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InternalServerError('No default connection set.');
    }
    
    //Prepare.
    $this->prepare($conn);
    
    //Get variables.
    $query = $this->getRawQuery($conn);
    $data = $this->data;
    
    //Replace occurrences of # with the database prefix used in this connection.
    $query = str_replace('#', $conn->config->prefix, $this->query);
    
    //Put data in query.
    $query = preg_replace_callback('~\?~', function()use(&$data){
      return array_shift($data);
    }, $query);
    
    //Return the query.
    return $query;
    
  }
  
  //Get the query, still having question-marks where the data is supposed to go.
  public function getRawQuery(Connection $conn = null)
  {
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InternalServerError('No default connection set.');
    }
    
    //Prepare.
    $this->prepare($conn);
    
    //Return the query.
    return $this->query;
    
  }
  
  //Short for $this->execute()->idx(0)->idx(0);
  public function scalar()
  {
    
    $result = call_user_func_array([$this, 'execute'], func_get_args());
    return $result->idx(0)->idx(0);
    
  }
  
  //Short for $this->execute()->idx(0);
  public function single()
  {
    
    $result = call_user_func_array([$this, 'execute'], func_get_args());
    return $result->idx(0);
    
  }
  
  //Executes the query and returns an Result object.
  public function execute(Connection $conn = null, \Closure $creator = null)
  {
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InternalServerError('No default connection set.');
    }
    
    //Create the query.
    $query = $this->getQuery($conn);
    
    //Check if we can execute without a resultSet.
    if($this->is_non_query){
      return $conn->exec($query);
    }
    
    //Execute.
    $result = $conn->query($query);
    
    //Errors?
    if(!$result){
      throw new \exception\Sql('Something went wrong while executing a query.');
    }
    
    //Use the right creator.
    $creator = (is_null($creator) ? $this->creator : $creator);
    
    //Create the data for the result.
    $data = [];
    foreach($result->fetchAll(\PDO::FETCH_ASSOC) as $row){
      $data[] = (is_null($creator) ? new \classes\sql\Row($row) : $creator($row));
    }
    
    //Return a new sql\Result.
    return new Result($data);
    
  }
  
  //Normalizes and sanitizes the query, the data and the datatypes.
  private function prepare(Connection $conn = null)
  {
  
    //We don't need to do anything if we already prepared.
    if($this->prepared){
      return $this;
    }
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InternalServerError('No default connection set.');
    }
    
    //Validate presense of a query.
    if(is_null($this->query)){
      throw new \exception\Restriction('Can not prepare() before having a query.');
    }
    
    //Data types.
    $dt =& $this->datatypes;
    
    //Data.
    $data =& $this->data;
    
    //Warn the programmer when the amount of data given does not equal the amount of ?'s.
    if(substr_count($this->query, '?') !== count($data)){
      throw new \exception\InternalServerError(
        'The amount of data given (%s nodes) does not equal the'.
        ' amount of places for the data (%s question-marks).',
        count($data),
        substr_count($this->query, '?')
      );
    }
    
    //Find the keys of undeclared datatypes.
    $keys = array_keys($dt, '?', true);
    
    //Auto-detect datatypes which aren't declared, and use them.
    foreach($keys as $key){
      switch(gettype($data[$key])){
        case 'integer': $dt[$key] = 'i'; break;
        case 'double': $dt[$key] = 'd'; break;
        default: $dt[$key] = 's'; break;
      }
    }
    
    //Sanitize the data.
    foreach($data as $key => $value)
    {
      
      //We are going to normalize and sanitize the data based on the type it is supposed to be.
      switch($dt[$key])
      {
        
        case 's'://tring
          
          //Cast it.
          $value = (string) $value;
          
          //Check if the casting worked.
          if(!is_string($value)){
            throw new \exception\Validation('Expecting a string.');
          }
          
          //Escape the value.
          $value = $conn->quote($value);
          
        break;
        case 'd'://ouble
          
          if(!is_numeric($value)){
            throw new \exception\Validation('Expecting a numeric value.');
          }
          
          $value = (double) $value;
          
        break;
        case 'i'://nteger
          
          if(!is_numeric($value)){
            throw new \exception\Validation('Expecting a numeric value.');
          }
          
          $value = (int) $value;
        
        break;
        
      }
      
      //Set the value back in the array.
      $data[$key] = $value;
      
    }
    
    //We are prepared! :D
    $this->prepared = true;
    
    //Enable chaining.
    return $this;
    
  }
  
}
