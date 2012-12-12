<?php namespace classes\sql;

abstract class BaseModel extends Row
{
  
  //Return the meta info provided by the model.
  public static function modelInfo()
  {
    
    //Create the array to put our information into.
    $info = [];
    
    //Add some of the meta-data provided by the model itself.
    foreach(['table_name', 'fields', 'relations'] as $var){
      if(isset(static::$$var)){
        $info[$var] = static::$$var;
      }
    }
    
    //Add the name of the model.
    $info['model_name'] = strstr(get_class(), '\\');
    
    //Return the wrapped info.
    return wrap($info);
    
  }
  
  //Return the meta info for the table.
  public static function tableInfo()
  {
    
    //The cache.
    static $table_info = [];
    
    //Our table name.
    $table_name = self::modelInfo()->table_name;
    
    //Is it cached?
    if(array_key_exists($table_name, $table_info)){
      return $table_info[$table_name];
    }
    
    //Create the new entry in our cache.
    $table_info[$table_name] = $tinfo = wrap([
      'auto_increment' => false,
      'primary_keys' => [],
      'fields' => []
    ]);
    
    //Fetch info from the database.
    tx('Sql')->exe("SHOW COLUMNS FROM `$table_name`")
    
    //Iterate over the columns.
    ->each(function($column)use(&$tinfo){
      
      //Check if it's an auto_increment.
      if($column->Extra == 'auto_increment'){
        $tinfo->auto_increment = $column->Field;
      }

      //Check if it's a primary key.
      if($column->Key == 'PRI' && !$tinfo->primary_keys->has($column->Field)){
        $tinfo->primary_keys[] = $column->Field;
      }

      //Set some essential information per column.
      $finfo = $column->having([
        'value' =>        'Default',
        'attributes' =>   'Type',
        'null_allowed' => 'Null',
        'extra' =>        'Extra',
        'key' =>          'Key'
      ]);
      
      //Set "null_allowed" to an actual boolean. Silly MySQL..
      $finfo->null_allowed = ($finfo->null_allowed == 'YES' ? true : false);
      
      //Parse attributes.
      $finfo->merge(
        
        //Do the parsing.
        $finfo->wrap('attributes')->parse('~'.
          '(?:^(?<type>\w+))'. //type
          '(?:\((?<arguments>[^\)]+)\))?'. //arguments
          '(?:(?<extra>(?:\s+\w+)*))'. //other attributes
        '~')
        
        //Extract the required data.
        ->having(['type', 'arguments', 'extra'])
        
        //Make a real array.
        ->unwrap()
        
      );
      
      //Parse the "extra" stuff.
      $finfo->extra = $finfo->wrap('extra')->trim()->lowercase()->split(' ')->toArray();
      
      //Unset the attributes attribute.
      unset($finfo->attributes);
      
      //Prettify the arguments.
      $finfo->arguments = $finfo->wrap('arguments')
        ->lowercase()
        ->split(',')
        ->map(function($arg){
          return $arg->trim(' \'');
        })
      ->unwrap();
      
      //Store.
      $tinfo->fields[$column->Field] = $finfo->unwrap();
      
    });

    return self::tableInfo();
    
  }
  
  //Protected properties.
  protected
    $component=null;
    
  //Private properties.
  private
    $caching=null,
    $cache=[];
  
  //Sets default values.
  public function __construct(array $values = [], \classes\Component $component)
  {
    
    //Set the component.
    $this->component = $component;
    
    //We're going to do some stuff with the fields.
    self::tableInfo()->fields
    
    //Apply default values.
    ->each(function($info, $field_name){
      
      if(!$info->value->isEmpty() || $info->check('null_allowed')){
        $this->arraySet($field_name, $info->value->get());
      }
      
      else{
        $this->arraySet($field_name, null);
      }
      
    });
    
    //Apply given values.
    $this->merge($values);
    
    #TODO: Convert bits.
    
  }
  
  //Add the possibility of custom getters to the arrayGet method.
  public function arrayGet($key)
  {
    
    //No custom getter?
    if(!method_exists($this, "get_$key")){
      return parent::arrayGet($key);
    }
    
    //Do we have a return value cached?
    if(array_key_exists($key, $this->cache)){
      return $this->cache[$key];
    }
    
    //Get the return value.
    $value = $this->{"get_$key"}();
    
    //Should we cache it?
    if($this->caching === true){
      $this->cache[$key] = $value;
    }
    
    //Clear the caching setting.
    $this->caching = null;
    
    //Return the value.
    return $value;
    
  }
  
  //Add the possibility of custom setters to the arraySet method.
  public function arraySet($key, $value)
  {
    
    //No custom setter?
    if(!method_exists($this, "set_$key")){
      return parent::arraySet($key, $value);
    }
    
    //Call the custom setter.
    return $this->{"set_$key"}($value);
    
  }
  
  //Clears the cache (for the given field). This applies to custom getters that use caching.
  public function clearCache($field_name=null)
  {
    
    //Clear a specific field?
    if(is_string($field_name))
    {
      
      //Only if it exists.
      if(array_key_exists($field_name, $this->cache)){
        unset($this->cache[$field_name]);
      }
      
    }
    
    //Clear the whole cache.
    else{
      $this->cache = [];
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Turns caching on, used inside custom getters to indicate the return value should be cached.
  protected function cache($bool=true)
  {
    
    $this->caching = (bool) $bool;
    
  }
  
  //Get the auto-increment field value (or key).
  public function ai($key=false)
  {
    
    if($key===true){
      return self::tableInfo()->auto_increment;
    }
    
    return $this->arrayGet(self::tableInfo()->auto_increment, true);
    
  }
  
  //Get an array with the values (or keys) of the primary key fields.
  public function pks($key=false)
  {
    
    if($key===true){
      return self::tableInfo()->primary_keys;
    }
    
    return $this->having(self::tableInfo()->primary_keys->toArray());
    
  }
  
  //Get an array with the values (or keys) of the fields in this model that have corresponding columns.
  public function databaseFields($key=false)
  {
    
    if($key===true){
      return self::tableInfo()->fields->keys();
    }
    
    return $this->having(self::tableInfo()->fields->keys()->toArray());
    
  }
  
  //Save the model to the database.
  public function save()
  {
    
    if($this->_must_insert()){
      $this->insert();
    }
    
    else{
      $this->update();
    }
    
    return $this;
    
  }
  
  //Save the model to the database as a new row.
  public function insert()
  {
    
    $data = $this->databaseFields()->toArray();
    
    $query = "INSERT INTO `".self::modelInfo()->table_name.
      "` (`".implode('`, `', array_keys($data))."`)".
      " VALUES(".implode(', ', array_fill(0, count($data), '?')).')';
    
    tx('Sql')->nonQuery($query, array_values($data))->execute();
    
    return $this;
    
  }
  
  //Save the model to the database by overwriting a previous row.
  public function update()
  {
    
    $data = $this->databaseFields();
    
    $query = 'UPDATE `'.self::modelInfo()->table_name.'`'.
      ' SET `'.$data->keys()->join('` = ?, `').'` = ? WHERE 1';
    
    $this->pks(true)->each(function($pk)use(&$query){
      $query .= " AND `$pk` = ?";
    });
    
    tx('Sql')->nonQuery($query, $data->values()->concat($this->pks()->values())->toArray())->execute();
    
    return $this;
    
  }
  
  //Returns true if the data in this model does not provide enough to update a specific row.
  private function _must_insert()
  {
    
    //Check if all the primary keys are set.
    foreach($this->pks() as $val){
      if(wrap($val)->isEmpty()){
        return true;
      }
    }
    
    //Check if the given primary keys refer to an existing row.
    $query = "SELECT COUNT(*) FROM `".self::modelInfo()->table_name."` WHERE 1";
    
    //Build the query.
    $this->pks(true)->each(function($pk)use(&$query){
      $query .= " AND `$pk` = ?";
    });
    
    //Execute the query and see if the row exists. If it does not, we can't update.
    if(tx('Sql')->query($query, $this->pks()->toArray())->scalar() != 1){
      return true;
    }
    
    //We can update.
    return false;
    
  }
  
}
