<?php namespace classes\route;

class PreProcessor extends BaseProcessor
{
  
  //Reroutes to the new given route. ([$discardFuture=false, ]$path)
  public function reroute()
  {
    
    //We need materials.
    $this->needsMaterials('to reroute');
    
    //Handle arguments.
    $args = func_get_args();
    
    //Are enough arguments given?
    if(empty($args)){
      throw new \exception\InvalidArgument('Too few arguments given.');
    }
    
    //get the path.
    $path = array_pop($args);
    
    //Validate the path.
    if(!is_string($path)){
      throw new \exception\InvalidArgument('Expecting $path to be string. %s given.', ucfirst(typeof($path)));
    }
    
    //Is a discard boolean given?
    if(!empty($args)){
      $discard = array_pop($args);
    }else{
      $discard = false;
    }
    
    //Validate the boolean.
    if(!is_bool($discard)){
      throw new \exception\InvalidArgument(
        'Expecting $discard to be boolean. %s given.',
        ucfirst(typeof($discard))
      );
    }
    
    //Reroute.
    $this->materials->router->reroute($path, !$discard);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Throw an Authorization exception when the user does not have one of the given permissions.
  public function permissions()
  {
    
    //Handle arguments.
    $permissions = wrap(func_get_args())->flatten();
    
    //Here we will gather the failed permissions.
    $failed = [];
    
    //Iterate.
    foreach($permissions as $permission)
    {
      
      //Check if the user has this permission.
      if(tx('User')->hasPermission($this->component()->name, $permission)){
        continue;
      }
      
      //The user does not have this permission. Add it to the failed-array.
      $failed[] = $permission;
      
    }
    
    //Did any permissions fail?
    if(count($failed) > 0)
    {
      
      //Create the friendly exception message.
      if(false){
        #TODO: Check database for permission description.
      }
      
      //Create the less friendly exception message.
      else{
        throw new \exception\Authorization(
          'You do not have the following permissions: %s', $permissions->join(', ')
        );
      }
      
    }
    
    //Enable chaining.
    return $this;
    
  }

}
