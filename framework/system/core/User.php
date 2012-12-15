<?php namespace core;

class User
{
  
  private
    $users;
  
  //Initialize the user array in the session.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'User class initializing.');
    
    //Create the users array in the session.
    if(!tx('Session')->exists('system', 'users')){
      tx('Session')->edit('system', 'users', []);
    }
    
    //Keep a reference to this "users" object.
    $this->users = tx('Session')->extract('system', 'users');
    
    //Enter a log entry.
    tx('Log')->message($this, 'User class initialized.');
    
  }
  
  //Return true if there is an active user.
  public function isLoggedIn()
  {
    
    if( ! ($active = $this->getActiveUser())){
      return false;
    }
    
    return $active->check('login');
    
  }
  
  //Return the array representing the active user.
  public function getActiveUser()
  {
    
    //Filter the users by "active" boolean.
    $active = wrap($this->users)->filter(function($node){
      return wrap($node)->check('active');
    });
    
    //This is bad.
    if($active->size() > 1){
      throw new \exception\InternalServerError('More than one active user.');
    }
    
    //No active user found.
    if($active->size() < 1){
      return false;
    }
    
    //Return the active user array.
    return $active->idx(0);
    
  }
  
  //Returns true if the currently active user has the given permission.
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
