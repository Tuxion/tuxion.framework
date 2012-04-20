<?php namespace core;

class User
{
  
  private
    $users;
  
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'class initialize', 'User class initializing.');
    
    //Create the system data if it doesn't exist.
    if( ! tx('Session')->system->isDefined()){
      tx('Session')->system->set([]);
    }
    
    //Create the users data if it doesn't exist.
    if( ! tx('Session')->system->users->isDefined()){
      tx('Session')->system->users->set([]);
    }
    
    //Keep a reference to this "users" object.
    $this->users =& tx('Session')->system->users;
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'class initialize', 'User class initialized.');
    
  }
  
  public function isLoggedIn()
  {
    
    if( ! ($active = $this->getActiveUser())){
      return false;
    }
    
    return $active->check('login');
    
  }
  
  public function getActiveUser()
  {
    
    $active = $this->users->filter(function($node){
      return $node->check('active');
    });
    
    if($active->size() > 1){
      throw new \exception\Unexpected('More than one active user.');
    }
    
    if($active->size() < 1){
      return false;
    }
    
    return $active->idx(0);
    
  }
  
  public function hasPermission($component, $key)
  {

    //Are we dealing with a logged-in user?
    if($user = $this->getActiveUser())
    {
      
      //Is it a super-user? They can do anything. ANYTHING!
      if($user->check('is_admin')){
        return true;
      }
      
      //Otherwise get the permission.
      return tx('Permissions')->getUserPermission($user->id->get(), $component, $key);
      
    }
    
    //Or are we are dealing with a guest?
    return tx('Permissions')->getGuestPermission($component, $key);
    
  }
  
}
