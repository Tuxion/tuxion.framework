<?php namespace classes\locators;

abstract class BaseDatabase extends Base
{
  
  //Protected static properties.
  protected static
    $readonly = ['id'],
    $cache = [];
    
  //Protected properties.
  protected
    $id,
    $table_name;
  
  //Set the properties by looking in the database.
  public function __construct($name, $location = null, Base $parent = null)
  {
    
    //Cast name to string to normalize for use in cache.
    $name = (string) $name;
    
    //Create a cache?
    if(!array_key_exists($this->table_name, self::$cache)){
      self::$cache[$this->table_name] = [];
    }
    
    //Reference the cache.
    $cache =& self::$cache[$this->table_name];
    
    //Get values from cache?
    if(array_key_exists($name, $cache)){
      $row = $cache[$name];
    }
    
    //Get values from the database.
    else
    {
      
      //Make the query.
      $query = sprintf(
        "SELECT * FROM %s WHERE %s = ?", $this->table_name,
        (preg_match('~^\d+$~', $name) == 1 ? 'id' : 'name')
      );
      
      //Execute the query and get the first row.
      $result = tx('Sql')->exe($query, $name);
      
      //Check if the resource was found in the database.
      if($result->isEmpty()){
        throw new \exception\ResourceMissing('Resource was not defined in the database.');
      }
      
      //Get the row.
      $row = $result->idx(0);
      
      //Store the row in the cache.
      $cache[$row->id] = $row;
      $cache[$row->name] = $row;
      
    }
    
    //Set the properties.
    $this->id = $row->id;
    parent::__construct($row->name, ($row->arrayExists('location') ? $row->location : $location), $parent);
    
  }
  
}
