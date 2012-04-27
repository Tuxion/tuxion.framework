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
  
  //Set the component after creating a new self.
  public function getSubController()
  {
    
    //Get the original return value.
    $new = call_user_func_array('parent::getSubController', func_get_args());
    
    //Break up it's path.
    $segments = explode('/', $new->base);
    
    //Validate it's path.
    if(count($segments) < 2 || $segments[0] !== 'com' || $segments[1][0] == '$'){
      throw new \exception\Restriction(
        'Invalid component-route "%s". Component routes must start with "com/<component_name>".',
        $new->base
      );
    }
    
    //Set the component.
    $new->setComponent($this->component);
    
    //Return it.
    return $new;
    
  }
  
  //Check the database to see if this end may overwrite the previous.
  public function end()
  {
    
    return call_user_func_array('parent::end', func_get_args());
    
  }
  
}
