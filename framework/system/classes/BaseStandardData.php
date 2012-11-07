<?php namespace classes;

abstract class BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type,
    $mimes=[];
  
  //Looks in the database to match the given mime-type to a converter-name.
  public static function getConverterByMime($mime)
  {
    
    //Do we have it in our cache?
    if(array_key_exists($mime, self::$mimes)){
      return self::getConverter(self::$mimes[$mime]);
    }
    
    //Get it from the database.
    tx('Sql')->exe('
      SELECT mt.name AS mime, oc.name AS converter FROM #system_mime_types AS `mt` 
      INNER JOIN #system_outputting_converters AS `oc` ON mt.id = oc.type_id
    ')
    
    //Iterate the results and add them to our map.
    ->each(function($row){
      self::$mimes[$row->mime] = $row->converter;
    });
    
    //Check again if we have the value.
    if(!array_key_exists($mime, self::$mimes)){
      throw new \exception\ResourceMissing('No converter found for type "%s".', $mime);
    }
    
    //Try again.
    return self::getConverterByMime($mime);
    
  }
  
  //Loads the given converter class and returns its object.
  public static function getConverter($name)
  {
    
    //Create file and class paths.
    $file = tx('Config')->paths->outputting.'/'.self::$type.'/converters/'.$name.'.php';
    $class = "\\outputting\\".self::$type."\\converters\\$name";
    
    //Load the class.
    load_class($file, $class);
    
    //Create and return the instance.
    return new $class($this);
    
  }
  
  //Private properties.
  private
    $data;
  
  //Set the data.
  public function __construct($data)
  {
    
    if(!static::accepts($data)){
      throw new \exception\InvalidArgument('Not accepting type: %s.', typeof($data));
    }
    
    $this->data = $data;
    
  }
  
  //Return the raw data.
  public function raw()
  {
    
    return $this->data;
    
  }
  
  //Output using one of our converters.
  public function outputAs($converter_name)
  {
    
    return self::getConverter($converter_name)->output($this);
    
  }
  
  //Return the templator with this data.
  public function createTemplator(\classes\Materials $materials)
  {
    
    //Create file and class paths.
    $file = tx('Config')->paths->outputting.'/'.static::$type.'/Templator.php';
    $class = '\\outputting\\'.static::$type.'\\Templator';
    
    //Load the class.
    load_class($file, $class);
    
    //Create and return the instance.
    return new $class($this, $materials);
    
  }
  
}
