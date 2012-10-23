<?php namespace classes;

class Component
{

  //Private static properties.
  private static
    $instances=[];
    
  //Public static properties.
  public static
    $active=null;
  
  //Try to find and return the component that could be identified with given identifier.
  public static function get($id, ArrayObject $cinfo=null)
  {
    
    //Somehow got back here?
    if($id instanceof self){
      return $id;
    }
    
    //Look in cache.
    if(array_key_exists($id, self::$instances)){
      return self::$instances[$id];
    }
    
    //no custom info given? Fill with database info.
    if(is_null($cinfo))
    {
    
      try{
        $cinfo = tx('Sql')->exe('
          SELECT * FROM `#system_components` WHERE `'.(is_numeric($id) ? 'id' : 'name').'` = ?',
          $id
        )[0];
      }
      
      catch(\exception\NotFound $e){
        throw new \exception\NotFound('Could not find component "%s" in the database.', $id);
      }
    
    }
    
    //Create the instance.
    self::$instances[$cinfo->id] = self::$instances[$cinfo->name] = $c = new self($cinfo);
    
    //Return the instance.
    return $c;
    
  }
  
  //Public properties.
  public
    $id,
    $name,
    $title;
    
  //Private properties.
  private
    $extended,
    $extending;
  
  //The constructor stores component info.
  public function __construct(ArrayObject $cinfo)
  {
    
    $this->id = $cinfo->id;
    $this->name = $cinfo->name;
    $this->title = $cinfo->title;
    
    tx('Log')->message($this, 'component loaded', $this->title);
    
  }
  
  //Return an ArrayObject containing a list of all components that extend this one.
  public function getExtendingComponents($recache=false)
  {
    
    //Return the cached object if possible.
    if(!empty($this->extending) && !$recache){
      return $this->extending;
    }
    
    //Fetch from the database.
    $result = tx('Sql')->exe('
      SELECT * FROM `#system_components` AS `c`
      INNER JOIN `#system_component_extensions` AS `ce` ON `ce`.`extended_by_id` = `c`.`id`
      WHERE `ce`.`component_id` = ?i',
      $this->id
    )
    
    //Map the Component objects.
    ->map(function($row){
      return self::get($row->id, $row);
    });
    
    //Return the result.
    return $result;
    
  }
  
  //Return an ArrayObject containing a list of all components extended by this one.
  public function getExtendedComponents($recache=false)
  {
    
    //Return the cached object if possible.
    if(!empty($this->extended) && !$recache){
      return $this->extended;
    }
    
    //Fetch from the database.
    $result = tx('Sql')->exe('
      SELECT * FROM `#system_components` AS `c`
      INNER JOIN `#system_component_extensions` AS `ce` ON `ce`.`component_id` = `c`.`id`
      WHERE `ce`.`extended_by_id` = ?i',
      $this->id
    )
    
    //Map the Component objects.
    ->map(function($row){
      return self::get($row->id, $row);
    });
    
    //Return the result.
    return $result;
    
  }
  
  //Load all controllers in this component, so that they may fill their Controller objects.
  public function loadControllers(Router $router)
  {
    
    //Glob the files!
    $files = files($this->getPath().'/controllers/*.php');
    
    //No controllers yet.
    $controllers = [];
    
    //No files? No nothing!
    if(empty($files)){
      return $controllers;
    }
    
    //Make a restore point.
    $c = route();
    
    //Iterate over the files.
    foreach($files as $file)
    {
    
      //Create the controller object for the next include.
      $controller = (new ComponentController(
        null,
        'com',
        "com/{$this->name}",
        $router,
        $this,
        basename($file, '.php')
      ));
      
      //Log.
      tx('Log')->message(
        $this,
        'created controller',
        $controller->component->title.': '.$controller->filename
      );
      
      //Set the magic route().
      route($controller);
      
      //Include the controller files.
      require($file);
      
      //Add to the controllers array.
      $controllers[] = $controller;
    
    }
    
    //Restore the magic c.
    route($c);
    
    //Enable chaining.
    return $controllers;
    
  }
  
  //Make sure the model with the given name is loaded and return the class string of the model.
  public function loadModel($model_name)
  {
    
    //String required.
    if(!is_string($model_name)){
      throw new \exception\InvalidArgument(
        'Expecting $model_name to be string. %s given.',
        typeof($model_name)
      );
    }
    
    //Make the class string.
    $class = 'components\\models\\'.$this->name.'\\'.$model_name;
    
    //Has this class been already loaded?
    if(class_exists($class, false)){
      return $class;
    }
    
    //Make the path.
    $path = $this->getPath().'/models/'.$model_name.'.php';
    
    //Check if the file exists.
    if(!file_exists($path)){
      throw new \exception\FileMissing($path);
    }
    
    //Require the file once.
    require_once($path);
    
    //Does the file not contain the class?
    if(!class_exists($class, false)){
      throw new \exception\Programmer(
        'The file "%s" does not contain a model named "%s" or is not in the right name-space.',
        $path, $model_name
      );
    }
    
    //Succeeded.
    return $class;
    
  }
  
  //Get model meta-data for the given model $model_name.
  public function getModelInfo($model_name)
  {
    
    $class = $this->loadModel($model_name);
    
    return $class::modelInfo();
    
  }
  
  //Get table meta-data for the given model $model_name.
  public function getTableInfo($model_name)
  {
    
    $class = $this->loadModel($model_name);
    
    return $class::tableInfo();
    
  }
  
  //Create a new instance of the given model model_name and return it.
  public function createModel($model_name, array $data = [])
  {
    
    $class = $this->loadModel($model_name);
    
    return new $class($data, $this);
    
  }
  
  //Create a Builder object which will return the given model when executed.
  public function fetchA($model_name, &$model=null)
  {
    
    return new sql\Builder(A, $this, $model_name, $model);
    
  }
  
  //Create a Builder object which will return a Result when executed.
  public function fetchAll($model_name, &$model=null)
  {
    
    return new sql\Builder(ALL, $this, $model_name, $model);
    
  }
  
  //Get the path that leads to this component.
  public function getPath()
  {
    
    return tx('Config')->paths->components.'/'.$this->name;
    
  }

}
