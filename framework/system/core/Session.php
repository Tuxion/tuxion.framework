<?php namespace core;

class Session extends \classes\DataBranch
{

  private
    $id;
  
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'Session class initializing.');
    
    session_start();
    $this->id = session_id();
    $this->set($_SESSION);
    session_unset();
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'Session class initialized.');
    
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
  
}
