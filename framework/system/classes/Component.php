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

    if(array_key_exists($id, self::$instances)){
      return self::$instances[$id];
    }
    
    if(is_null($cinfo))
    {
    
      try{
        $cinfo = tx('Sql')->query('
          SELECT * FROM `#system_components` WHERE `'.(is_numeric($id) ? 'id' : 'name').'` = ?',
          $id
        )[0];
      }
      
      catch(\exception\NotFound $e){
        throw new \exception\NotFound('Could not find component "%s" in the database.', $id);
      }
    
    }
    
    self::$instances[$cinfo->id] = self::$instances[$cinfo->name] = $c = new $this($cinfo);
    
    return $c;
    
  }
  
  //Public properties.
  public
    $id,
    $name,
    $title;
    
  //Private properties.
  private
    $controllers=[],
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
    $result = tx('Sql')->query('
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
    $result = tx('Sql')->query('
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
  
  //Load all controllers in this component, so that they may fill their Route objects.
  public function loadControllers(Router $router)
  {
    
    //Glob the files!
    $files = files($this->getPath().'/controllers/*.php');
    
    //No files? No nothing!
    if(empty($files)){
      return $this;
    }
    
    //Prepare variables that can be used inside the controller.
    $controller = (new ComponentController(null, 'com', "com/{$this->name}"))
      ->setRouter($router)
      ->setComponent($this);
    
    $c = c();
    c($controller);
    $component = $this;
    
    //Include the controller files.
    foreach($files as $file){
      require($file);
    }
    
    c($c);
    
    //Enable chaining.
    return $this;
    
  }
  
  
  //Get model meta-data for the given model $name.
  public function getModelInfo($name)
  {
    
    # code...
    
  }
  
  //Create a new instance of the given model name and return it.
  public function createModel($name)
  {
    
    
    
  }
  
  //Get the path that leads to this component.
  public function getPath()
  {
    
    return tx('Config')->paths->components.'/'.$this->name;
    
  }

}
