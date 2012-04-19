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
  
  //Return the controller with the given $name from this component.
  public function getController($name)
  {
    
    if(array_key_exists($name, $this->controllers)){
      return $this->controllers[$name];
    }
    
    $path = $this->getPath().'/controllers/'.$name.'.php';
    $class = "\\components\\controllers\\{$this->name}\\$name";
    
    if(!file_exists($path)){
      throw new \exception\FileMissing($path);
    }
    
    require_once($path);
    
    if(!class_exists($class, false)){
      throw new \exception\NotFound('Could not find class "%s".', $class);
    }
    
    $this->controllers[$name] = $c = new $class;
    return $c;
    
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
