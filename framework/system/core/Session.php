<?php namespace core;

use \classes\data\ArrayWrapper;

class Session extends ArrayWrapper
{

  private
    $id;
  
  ##
  ## MANAGEMENT METHODS
  ##
  
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Session class initializing.');
    
    session_start();
    $this->id = session_id();
    $this->set($_SESSION);
    session_unset();
    
    //Enter a log entry.
    tx('Log')->message($this, 'Session class initialized.');
    
  }
  
  public function __destruct()
  {
    
    $_SESSION = $this->toArray();
    session_write_close();
    
  }
  
  public function regenerate()
  {
    
    session_regenerate_id();
    $this->id = session_id();
    
    return $this;
    
  }
  
  public function destroy()
  {
    
    $this->data = $_SESSION = [];
    
    if(ini_get("session.use_cookies")){
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    
    session_destroy();
    
    return $this;
    
  }
  
  public function id()
  {
    
    return $this->id;
    
  }
  
  
  ##
  ## MODIFIER METHODS
  ##
  
  //Edit session data.
  //edit($key[, $key, ...], $value)
  public function edit()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Must have at least 2 arguments.
    if(count($args) < 2){
      throw new \exception\InvalidArgument(
        'Expecting at least two arguments. A key and a value. %s Given.', count($args)
      );
    }
    
    //Pop the value off.
    $value = array_pop($args);
    
    //Set the first "current".
    $current =& $this->arr;
    
    //Find or create the arrays that correspond to the keys.
    for($i=0, $total=count($args); $i < $total, list(, $key) = each($args); $i++)
    {
      
      //Set the the given value?
      if($i == $total){
        $current[$key] = $value;
        break;
      }
      
      //Create the node?
      if(!array_key_exists($key, $current)){
        $current[$key] = [];
      }
      
      //Store the node.
      $current =& $current[$key];
      
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return true if the given keys lead to existing data.
  public function exists()
  {
    
    //Handle arguments.
    $args = func_get_args();
    $current =& $this->arr;
    
    foreach($args as $key)
    {
      
      if(!array_key_exists($key, $current)){
        return false;
      }
      
      $current =& $current[$key];
      
    }
    
    return true;
    
  }
  
}
