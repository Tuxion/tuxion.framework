<?php namespace classes;

class Component
{

  public
    $id,
    $name,
    $title;
    
  private
    $controllers=[];
    
  private static
    $instances=[];
    
  public static function get($id)
  {
    
    if(array_key_exists($id, self::$instances)){
      return self::$instances[$id];
    }
    
    try{
      $cinfo = tx('Sql')->query('SELECT * FROM `#system_components` WHERE `'.(is_int($id) ? 'id' : 'name').'` = ?', $id)[0];
    }
    
    catch(\exception\NotFound $e){
      throw new \exception\NotFound('Could not find component "%s" in the database.', $id);
    }
    
    self::$instances[$cinfo->id] = self::$instances[$cinfo->name] = $c = new self($cinfo);
    
    return $c;
    
  }
  
  public function __construct(ArrayObject $cinfo)
  {
    
    $this->id = $cinfo->id;
    $this->name = $cinfo->name;
    $this->title = $cinfo->title;
    
  }
  
  public function getController($name)
  {
    
    if(array_key_exists($name, $this->controllers)){
      return $this->controllers[$name];
    }
    
    $path = $this->getPath().'/controllers/'.$name.'.php';
    $class = "\\components\\{$this->name}\\controllers\\$name";
    
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
  
  public function createModel($name)
  {
    
    
    
  }
  
  public function getPath()
  {
    
    return tx('Config')->paths->components.'/'.$this->name;
    
  }

}
