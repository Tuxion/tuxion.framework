<?php namespace core;

class Permissions
{

  private
    $user_cache = [],
    $guest_cache = [];
  
  public function getUserPermission($user_id, $component, $key)
  {
    
    //Create the cache array for this user, if it doesn't exist yet.
    if(!array_key_exists($user_id, $this->user_cache)){
      $this->user_cache[$user_id] = [];
    }
    
    //Get the cache array for this user.
    $cache =& $this->user_cache[$user_id];
    
    //Get component info.
    $cinfo = tx('Component')[$component];
    
    //Check our cache.
    if(array_key_exists($cinfo->id, $cache))
    {
      
      //If the value is not in the cache, the programmer must be checking for a non-existing permission.
      if( ! array_key_exists($key, $cache[$cinfo->id])){
        throw new \exception\NotFound('The permission "%s" does not exist for "%s".', $key, $cinfo->title);
      }
      
      //If the value is still null, something went wrong.
      if(is_null($cache[$cinfo->id][$key])){
        throw new \exception\NotFound('Error reading permission-cache: "%s" was not found even though user(%s) permissions for "%s" have been cached.', $key, $user_id, $cinfo->title);
      }
      
      //The permission exists in the cache. Return it.
      return $cache[$cinfo->id][$key];
      
    }
    
    //If the cache didn't exist yet, we will fetch it.
    $permissions = tx('Sql')->query('SELECT `key` FROM `#system_component_permissions` WHERE `component_id` = ?i', $cinfo->id)->map(function($row){
      return $row->key;
    })->toArray();
    
    //And put its results as unset keys in our cache array.
    $cache[$cinfo->id] = array_fill_keys($permissions, null);
    
    //Make a reference to the user cache.
    $user_cache =& $cache[$cinfo->id];
    
    //If database cache is enabled.
    if(tx('Config')->config->permission_caching == true)
    {
      
      //Read the cache, and put it into the cache object.
      $this->readCache($user_id, $cinfo->id)->each(function($row)use(&$user_cache){
        $user_cache[$row->key] = ($row->value >= YES);
      });
      
      //Then retry.
      return $this->getUserPermission($user_id, $cinfo->id, $key);
    
    }
    
    //If the database cache is disabled, we will calculate.
    $this->calculateUserPermissions($user_id, $cinfo->id)->each(function($value, $key)use(&$user_cache){
      $user_cache[$key] = $value;
    });
    
    //And then retry.
    return $this->getUserPermission($user_id, $cinfo->id, $key);
    
  }
  
  public function getGuestPermission($component, $key)
  {
    
    //Get the cache array for the guest.
    $cache =& $this->guest_cache;
    
    //Get component info.
    $cinfo = tx('Component')[$component];
    
    //Check our cache.
    if(array_key_exists($cinfo->id, $cache))
    {
      
      //If the value is not in the cache, the programmer must be checking for a non-existing permission.
      if( ! array_key_exists($key, $cache[$cinfo->id])){
        throw new \exception\NotFound('The permission "%s" does not exist for "%s".', $key, $cinfo->title);
      }
      
      //If the value is still null, something went wrong.
      if(in_null($key, $cache[$cinfo->id])){
        throw new \exception\NotFound('Error reading permission-cache: "%s" was not found even though guest permissions for "%s" have been cached.', $key, $cinfo->title);
      }
      
      //The permission exists in the cache. Return it.
      return $cache[$cinfo->id][$key];
      
    }
    
    //If the cache didn't exist yet, we will fetch it.
    $permissions = tx('Sql')->query('SELECT `key` FROM `#system_component_permissions` WHERE `component_id` = ?i', $cinfo->id)->map(function($row){
      return $row->key;
    })->toArray();
    
    //And put its results as unset keys in our cache array.
    $cache[$cinfo->id] = array_fill_keys($permissions, null);
    
    //Make a reference to the guest cache.
    $guest_cache =& $cache[$cinfo->id];
    
    //Fetch role permissions.
    $roles = tx('Sql')->query('SELECT * FROM `#system_roles` WHERE `is_guest_role` = 1 AND `component_id` = ?i', $cinfo->id);
    
    //Check for errors.
    if($roles->num() > 1){
      throw new \exception\Restriction('Component "%s" assigned multiple roles to guests.', $cinfo->title);
    }
    
    //See if any roles were assigned.
    if($roles->num() == 1)
    {
      
      //Fetch the actual permissions.
      $role_permissions = tx('Sql')->query('SELECT * FROM `#system_permissions_role` WHERE role_id = ?i', $roles[0]->id);
      
      //If there were, add them to the cache.
      $role_permissions->each(function($row)use(&$guest_cache){
        $guest_cache[$row->key] = $row->value;
      });
      
    }
    
    //Now fetch guest permissions.
    $guest_permissions = tx('Sql')->query('SELECT * FROM `#system_permissions_guest` WHERE `component_id` = ?i', $cinfo->id);
    
    //And add those to the cache.
    $guest_permissions->each(function($row)use(&$guest_cache){
      $guest_cache[$row->key] = $row->value;
    });
    
    //Then retry.
    return $this->getGuestPermission($cinfo->id, $key);
    
  }
  
  private function calculateUserPermissions($user_id, $component_id, $key = null)
  {
    
    $results = tx('Sql')->queries([
      
      //all groups role
      ['SELECT *
        FROM `#system_permissions_role` AS `pr`
        INNER JOIN `#system_roles_to_user_groups` AS `rtug` ON `pr`.`role_id` = `rtug`.`role_id`
        WHERE `rtug`.`user_group_id` IN (SELECT `user_group_id` FROM `#system_user_to_user_groups` WHERE `user_id` = ?i)',
        $user_id
      ],
      
      //check all groups always/never - in case of conflict: programmer exception
      ['SELECT *
        FROM `#system_permissions_user_group` AS `pug`
        WHERE `pug`.`user_group_id` IN (SELECT `user_group_id` FROM `#system_user_to_user_groups` WHERE `user_id` = ?i)',
        $user_id
      ],
      
      //check default group role always/never
      ['SELECT *
        FROM `#system_permissions_role` AS `pr`
        INNER JOIN `#system_roles_to_user_groups` AS `rtug` ON `pr`.`role_id` = `rtug`.`role_id`
        WHERE `rtug`.`user_group_id` = (SELECT `default_group_id` FROM `#system_roles_to_user_groups` WHERE `id` = ?i)',
        $user_id
      ],
      
      //check default group always/never
      ['SELECT *
        FROM `#system_permissions_user_group` AS `pug`
        INNER JOIN `#system_user_to_user_groups` AS `utug` ON `pug`.`id` = `utug`.`user_group_id`
        INNER JOIN `#system_users` AS `u` ON `utug`.`user_id` = `u`.`id`
        WHERE `u`.`default_group_id` = `pug`.`user_group_id` AND `utug`.`user_id` = ?i AND `pug`.`component_id` = ?i',
        $user_id, $component_id
      ],
      
      //check user-level role yes/no
      ['SELECT *
        FROM `#system_permissions_role` AS `pr`
        INNER JOIN `#system_roles_to_users` AS `rtu` ON `pr`.`role_id` = `rtu`.`role_id`
        INNER JOIN `#system_roles` AS `r` ON `pr`.`role_id` = `r`.`id`
        WHERE `rtu`.`user_id` = ?i AND `r`.`component_id = ?i`',
        $user_id, $component_id
      ],
      
      //check user-level yes/no
      ['SELECT * FROM `#system_permissions_user` WHERE `user_id` = ?i AND `component_id` = ?i', $user_id, $component_id]
    
    ]);
    
    trace($results);
    return new \classes\ArrayObject(['eatpie' => true, 'killpeople' => null]);
    
  }
  
  private function readCache($user_id, $component_id)
  {
    
    return tx('Sql')->query('SELECT * FROM `#system_permission_cache` WHERE `user_id` = ?i AND `component_id` = ?i', $user_id, $component_id);
    
  }
  
  private function writeCache($user_id, $component_id, $data)
  {
  
  
  
  }
  
}