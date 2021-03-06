<?php namespace classes;

abstract class BaseStandardData
{
  
  //Protected static properties.
  protected static
    $type,
    $mimes=[];
  
  //Private properties.
  private
    $data;
  
  //Loads and returns the converter of the given name.
  public static function loadConverter($name)
  {
    
    //Create file and class paths.
    $file = tx('Config')->paths->outputting.'/'.static::$type.'/converters/'.$name.'.php';
    $class = "\\outputting\\".static::$type."\\converters\\$name";
    
    //Load the class.
    load_class($file, $class);
    
    //Return the class name.
    return $class;
    
  }
  
  //Loads and returns the converter corresponding to the given mime-type.
  public function loadConverterByMime($mime)
  {
    
    //Do we have it in our cache?
    if(array_key_exists($mime, self::$mimes)){
      return self::loadConverter(self::$mimes[$mime]);
    }
    
    //Get it from the database.
    tx('Sql')->exe('
      SELECT mt.name AS mime, oc.name AS converter FROM #system_mime_types AS `mt` 
      INNER JOIN #system_outputting_converters AS `oc` ON mt.id = oc.mime_type_id
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
    return self::loadConverter(self::$mimes[$mime]);
    
  }
  
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
  
  //Looks in the database to match the given mime-type to a converter-name.
  public function createConverterByMime($mime)
  {
    
    //Get the class.
    $class = self::loadConverterByMime($mime);
    
    //Return an instance of it.
    return new $class($this);
    
  }
  
  //Loads the given converter class and returns its object.
  public function createConverter($name)
  {
    
    //Get the class.
    $class = self::loadConverter($name);
    
    //Create and return the instance.
    return new $class($this);
    
  }
  
  //Return the templator with this data.
  public function createTemplator(Materials $materials)
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
