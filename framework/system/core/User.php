<?php namespace core;

class User
{
  
  private
    $cache = [],
    $users;
  
  public function init()
  {
    $this->users =& tx('Session')->system->users;
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
    
    $active = $this->users->filter(function(){
      return $this->check('active');
    });
    
    if($active->size() > 1){
      throw new \exception\Unexpected('More than one active user.');
    }
    
    if($active->size() < 1){
      return false;
    }
    
    return $active->idx(0);
    
  }
  
  public function hasPermission($component, $permission)
  {
    
    //Are we dealing with a logged-in user?
    if($user = $this->getActiveUser())
    {
      
      //Is it a super-user? They can do anything. ANYTHING!
      if($user->check('is_admin')){
        return true;
      }
      
    }
    
    //Get component info.
    $cinfo = tx('Component')[$component];
    
    //Check our cache.
    if(array_key_exists($cinfo->id, $this->cache))
    {
      
      //If the value is not in the cache, something went wrong and we will deny this user the permission.
      if( ! array_key_exists($permission, $this->cache[$cinfo->id])){
        return false;
      }
      
      //The permission exists in the cache. Return it.
      return $this->cache[$cinfo->id][$permission];
      
    }
    
    else{
      $this->cache[$cinfo->id] = [];
    }
    
    //If we get to here, the permission hasn't been cached. We will have to calculate it.
    if(!$user)
    {
      
      //Guests are easy.
      tx('Sql')->query('SELECT * FROM `#system_permissions_guest` WHERE `component_id` = ?i', $cinfo->id)->each(function($row)use($cinfo){
        $this->cache[$row->component_id][$row->key] = ($row->value == YES);
      });
      
      //After fetching the permissions, we simply check again!
      return $this->hasPermission($component, $permission);
      
    }
    
    //check user-level yes/no
    //check user-level role yes/no
    //check default group always/never
    //check all groups always/never - in case of conflict: programmer exception
    //check default group yes/no
    //check default group role always/never
    //check all groups role always/never - in case of conflict: programmer exception
    //check default group role yes/no
    
  }
  
}










