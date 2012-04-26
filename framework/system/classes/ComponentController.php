<?php namespace classes;

class ComponentController extends Controller
{
  
  //Private properties.
  private
    $component;
  
  //Set the component.
  public function setComponent(Component $com)
  {
    
    $this->component = $com;
    return $this;
    
  }
  
  //Set the component after create a new self.
  public function get()
  {
    
    $new = call_user_func_array('parent::get', func_get_args());
    
    $segments = explode('/', $new->base);
    
    if(count($segments) < 2 || $segments[0] !== 'com' || $segments[1][0] == '$'){
      throw new \exception\Restriction(
        'Invalid component-route "%s". Component routes must start with "com/<component_name>".',
        $new->base
      );
    }
    
    $new->setComponent($this->component);
    return $new;
    
  }
  
  //Check the database to see if this end may overwrite the previous.
  public function end()
  {
    
    parent::end($description, $callback);
    
  }
  
}
