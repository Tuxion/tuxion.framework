<?php namespace classes;

class Component
{

  //Public properties.
  public
    $id,
    $name,
    $title;
    
  //Private properties.
  private
    $controllers=[];
  
  //Private static properties.
  private static
    $instances=[];
  
  //Try to find and return the component that could be identified with given identifier.
  public static function get($id)
  {
    
    if(array_key_exists($id, self::$instances)){
      return self::$instances[$id];
    }
    
    try{
      $cinfo = tx('Sql')->query('SELECT * FROM `#system_components` WHERE `'.(is_numeric($id) ? 'id' : 'name').'` = ?', $id)[0];
    }
    
    catch(\exception\NotFound $e){
      throw new \exception\NotFound('Could not find component "%s" in the database.', $id);
    }
    
    self::$instances[$cinfo->id] = self::$instances[$cinfo->name] = $c = new self($cinfo);
    
    return $c;
    
  }
  
  //The constructor stores component info.
  public function __construct(ArrayObject $cinfo)
  {
    
    $this->id = $cinfo->id;
    $this->name = $cinfo->name;
    $this->title = $cinfo->title;
    
  }
  
  //Load all controllers in this component, so that they may fill their Route objects.
  public function loadControllers()
  {
    
    $route = $R = new \classes\Router(null, "com/{$this->name}", 'com');
    
    foreach(glob($this->getPath().'/controllers/*.php') as $file){
      require_once($file);
    }
    
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
