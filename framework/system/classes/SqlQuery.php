<?php namespace classes;

class SqlQuery
{
  
  public
    $connection=null;
    
  private
    $prepared=false,
    $query,
    $datatypes=[],
    $data=[];
  
  //Initialize the SqlQuery instance, optionally passing a default connection and query information.
  public function __construct()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //A default connection as first argument?
    if(!empty($args) && $args[0] instanceof \classes\SqlConnection){
      $this->connection = array_shift($args);
    }
    
    //An array with query and data?
    if(!empty($args) && is_array($args[0]))
    {
      
      $q = array_shift($args);
      
      if(!empty($q)){
        $this->setQuery(array_shift($q));
      }
      
      if(!empty($q)){
        $this->setData($q);
      }
      
    }
    
    //A query?
    if(!empty($args) && is_string($args[0])){
      $this->setQuery(array_shift($args));
    }
    
    //Query-data?
    if(!empty($args)){
      $this->setData($args);
    }
      
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
        'Your query contains the ";"-character.'.
        ' Note that SqlQuery does not execute multi-queries. Use SqlMultiQuery for that.'
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
    
    $this->data = $data;
    $this->prepared = false;
    
    return $this;
    
  }
  
  //What do you think?
  public function isPrepared()
  {
    return $this->prepared;
  }
  
  //Get the query with the data inserted into it.
  public function getQuery(SqlConnection $conn = null)
  {
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InputMissing('No default connection set.');
    }
    
    //Prepare.
    $this->prepare($conn);
    
    //Get variables.
    $query = $this->getRawQuery($conn);
    $data = $this->data;
    
    //Replace occurences of # with the database prefix used in this connection.
    $query = str_replace('#', $conn->config->prefix, $this->query);
    
    //Put data in query.
    $query = preg_replace_callback('~\?~', function()use(&$data){
      return array_shift($data);
    }, $query);
    
    //Return the query.
    return $query;
    
  }
  
  //Get the query, still having questionmarks were the data is supposed to go.
  public function getRawQuery(SqlConnection $conn = null)
  {
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InputMissing('No default connection set.');
    }
    
    //Prepare.
    $this->prepare($conn);
    
    //Return the query.
    return $this->query;
    
  }
  
  //Executes the query and returns an SqlResult object.
  public function execute(SqlConnection $conn = null, $model='\\classes\\SqlRow')
  {
    
    //Get connection.
    if(is_null($conn)){
      $conn = $this->connection;
    }
    
    //Check connection.
    if(is_null($conn)){
      throw new \exception\InputMissing('No default connection set.');
    }
    
    $query = $this->getQuery($conn);
    $result = $conn->query($query);
    
    if(!$result){
      throw new \exception\Sql('Something went wrong while executing a query.');
    }
    
    $data = [];
    foreach($result->fetchAll(\PDO::FETCH_ASSOC) as $row){
      $data[] = new $model($row);
    }
    
    return new SqlResult($data);
    
  }
  
  //Normalizes and sanitizes the query, the data and the datatypes.
  private function prepare(SqlConnection $conn = null)
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
      throw new \exception\InputMissing('No default connection set.');
    }
    
    //Validate presense of a query.
    if(is_null($this->query)){
      throw new \exception\Programmer('Can not prepare() before having a query.');
    }
    
    //Data types.
    $dt =& $this->datatypes;
    
    //Find the keys of undeclared datatypes.
    $keys = array_keys($dt, '?', true);
    
    //Auto-detect datatypes which aren't declared, and use them.
    foreach($keys as $key){
      switch(gettype($this->data[$key])){
        case 'integer': $dt[$key] = 'i'; break;
        case 'double': $dt[$key] = 'd'; break;
        default: $dt[$key] = 's'; break;
      }
    }
    
    //Data.
    $data =& $this->data;
    
    //Warn the programmer when the amount of data given does not equal the amount of ?'s.
    if(substr_count($this->query, '?') !== count($data)){
      throw new \exception\InputMissing(
        'The amount of data given (%s nodes) does not equal the'.
        ' amount of places for the data (%s questionmarks).',
        count($data),
        substr_count($this->query, '?')
      );
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
  
  //Converts a php value to a mySQL value.
  private function _toSql($value)
  {
    
    switch(strtolower(gettype($value))){
      case 'string': $value = (/*($value == 'NULL') ? 'NULL' : */"'$value'"); break;
      case 'integer': case 'double': break;
      case 'boolean': $value = (($value == true) ? 1 : 0); break;
      case 'null': $value = 'NULL'; break;
    }
    
    return $value;
    
  }
  
}
