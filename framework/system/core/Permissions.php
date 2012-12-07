<?php namespace core;

class Permissions
{

  private
    $user_cache = [],
    $guest_cache = [];
  
  //Initialize.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Permissions class initializing.');
    
    //Enter a log entry.
    tx('Log')->message($this, 'Permissions class initialized.');
    
  }
  
  //Return the value of the given permission for the given user.
  public function getUserPermission($user_id, $component, $key)
  {
    
    //Create the cache array for this user, if it doesn't exist yet.
    if(!array_key_exists($user_id, $this->user_cache)){
      $this->user_cache[$user_id] = [];
    }
    
    //Get the cache array for this user.
    $cache =& $this->user_cache[$user_id];
    
    //Get component info.
    $cinfo = \classes\Component::get($component);
    
    //Check our cache.
    if(array_key_exists($cinfo->id, $cache))
    {
      
      //If the value is not in the cache, the programmer must be checking for a non-existing permission.
      if( ! array_key_exists($key, $cache[$cinfo->id])){
        throw new \exception\Permission('The permission "%s" does not exist for "%s".', $key, $cinfo->title);
      }
      
      //If the value is still null, something went wrong.
      if(is_null($cache[$cinfo->id][$key])){
        throw new \exception\Permission(
          'Error reading permission-cache: "%s" was not found even though user(%s) '.
          'permissions for "%s" have been cached.',
          $key, $user_id, $cinfo->title
        );
      }
      
      //The permission exists in the cache. Return it.
      return $cache[$cinfo->id][$key];
      
    }
    
    //If the cache didn't exist yet, we will fetch it.
    $permissions = tx('Sql')->exe(
      'SELECT `key` FROM `#system_component_permissions` WHERE `component_id` = ?i',
      $cinfo->id
    )->map(function($row){
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
    return $this->getUserPermission($user_id, $cinfo, $key);
    
  }
  
  //Return the value for the given permission for guests.
  public function getGuestPermission($component, $key)
  {
    
    //Get the cache array for the guest.
    $cache =& $this->guest_cache;
    
    //Get component info.
    $cinfo = \classes\Component::get($component);
    
    //Check our cache.
    if(array_key_exists($cinfo->id, $cache))
    {
      
      //If the value is not in the cache, the programmer must be checking for a non-existing permission.
      if( ! array_key_exists($key, $cache[$cinfo->id])){
        throw new \exception\Permission('The permission "%s" does not exist for "%s".', $key, $cinfo->title);
      }
      
      //If the value is still null, something went wrong.
      if(is_null($cache[$cinfo->id][$key])){
        throw new \exception\Permission(
          'Error reading permission-cache: "%s" was not found even though guest '.
          'permissions for "%s" have been cached.',
          $key, $cinfo->title
        );
      }
      
      //The permission exists in the cache. Return it.
      return $cache[$cinfo->id][$key];
      
    }
    
    //If the cache didn't exist yet, we will fetch it.
    $permissions = tx('Sql')->exe(
      'SELECT `key` FROM `#system_component_permissions` WHERE `component_id` = ?i',
      $cinfo->id
    )->map(function($row){
      return $row->key;
    })->toArray();
    
    //And put its results as unset keys in our cache array.
    $cache[$cinfo->id] = array_fill_keys($permissions, false);
    
    //Make a reference to the guest cache.
    $guest_cache =& $cache[$cinfo->id];
    
    //Fetch role permissions.
    $roles = tx('Sql')->exe(
      'SELECT * FROM `#system_roles` WHERE `is_guest_role` = 1 AND `component_id` = ?i',
      $cinfo->id
    );
    
    //Check for errors.
    if($roles->num() > 1){
      throw new \exception\Restriction('Component "%s" assigned multiple roles to guests.', $cinfo->title);
    }
    
    //See if any roles were assigned.
    if($roles->num() == 1)
    {
      
      //Fetch the actual permissions.
      $role_permissions = tx('Sql')->exe(
        'SELECT * FROM `#system_permissions_role` WHERE role_id = ?i',
        $roles[0]->id
      );
      
      //If there were, add them to the cache.
      $role_permissions->each(function($row)use(&$guest_cache){
        $guest_cache[$row->key] = $row->value;
      });
      
    }
    
    //Now fetch guest permissions.
    $guest_permissions = tx('Sql')->exe(
      'SELECT * FROM `#system_permissions_guest` WHERE `component_id` = ?i', $cinfo->id
    );
    
    //And add those to the cache.
    $guest_permissions->each(function($row)use(&$guest_cache){
      $guest_cache[$row->key] = ($row->value > 0);
    });
    
    //Then retry.
    return $this->getGuestPermission($cinfo, $key);
    
  }
  
  //Calculate the outcome of permissions in a specific component for a given user.
  private function calculateUserPermissions($user_id, $component_id)
  {
    
    //Get a reference to component info for pretty errors.
    $cinfo = \classes\Component::get($component_id);
    
    //Define queries.
    $queries = [
      
      //Get permissions for the global group role.
      ['SELECT `key`, `value`
        FROM `#system_permissions_role` AS `pr`
        WHERE `role_id` = (
          SELECT `id` FROM `#system_roles` WHERE `is_user_global_role` = 1 AND `component_id` = ?i
        )',
        $component_id
      ],
      
      //Get permissions for the global group.
      ['SELECT `key`, `value`
        FROM `#system_permissions_user_global` AS `pugl`
        WHERE `component_id` = ?i',
        $component_id
      ],
      
      //Get permissions for the roles assigned to every group that the user is a member of.
      ['SELECT `key`, `value`
        FROM `#system_permissions_role` AS `pr`
        INNER JOIN `#system_roles_to_user_groups` AS `rtug` ON `pr`.`role_id` = `rtug`.`role_id`
        INNER JOIN `#system_roles` AS `r` ON `pr`.`role_id` = `r`.`id`
        WHERE `rtug`.`user_group_id` IN (
          SELECT `user_group_id` FROM `#system_user_to_user_groups` WHERE `user_id` = ?i
        )
        AND `r`.`component_id` = ?i',
        $user_id, $component_id
      ],
      
      //Get permissions for every group that the user is a member of.
      ['SELECT `key`, `value`
        FROM `#system_permissions_user_group` AS `pug`
        WHERE `pug`.`user_group_id` IN (
          SELECT `user_group_id` FROM `#system_user_to_user_groups` WHERE `user_id` = ?i
        )
        AND `component_id` = ?i',
        $user_id, $component_id
      ],
      
      //Get permissions for the role assigned to the user's default group.
      ['SELECT `key`, `value`
        FROM `#system_permissions_role` AS `pr`
        INNER JOIN `#system_roles_to_user_groups` AS `rtug` ON `pr`.`role_id` = `rtug`.`role_id`
        INNER JOIN `#system_roles` AS `r` ON `pr`.`role_id` = `r`.`id`
        WHERE `rtug`.`user_group_id` = (SELECT `default_group_id` FROM `#system_users` WHERE `id` = ?i)
        AND `r`.`component_id` = ?i',
        $user_id, $component_id
      ],
      
      //Get permissions assigned to the user's default group.
      ['SELECT `key`, `value`
        FROM `#system_permissions_user_group` AS `pug`
        INNER JOIN `#system_user_to_user_groups` AS `utug` ON `pug`.`id` = `utug`.`user_group_id`
        INNER JOIN `#system_users` AS `u` ON `utug`.`user_id` = `u`.`id`
        WHERE `u`.`default_group_id` = `pug`.`user_group_id` 
          AND `utug`.`user_id` = ?i
          AND `pug`.`component_id` = ?i',
        $user_id, $component_id
      ],
      
      //Get permissions for the role assigned to this user.
      ['SELECT `key`, `value`
        FROM `#system_permissions_role` AS `pr`
        INNER JOIN `#system_roles_to_users` AS `rtu` ON `pr`.`role_id` = `rtu`.`role_id`
        INNER JOIN `#system_roles` AS `r` ON `pr`.`role_id` = `r`.`id`
        WHERE `rtu`.`user_id` = ?i AND `r`.`component_id` = ?i',
        $user_id, $component_id
      ],
      
      //Get user-specific permissions.
      ['SELECT `key`, `value` FROM `#system_permissions_user` WHERE `user_id` = ?i AND `component_id` = ?i',
        $user_id, $component_id
      ]
    
    ];
    
    $results = tx('Sql')->queries($queries);
    $permissions = [];
    
    //Merge the global role permissions with the global permissions.
    $global_permissions = array_merge($results[0]->toArray(false), $results[1]->toArray(false));
    
    //Merge group role permissions with group permissions.
    $group_permissions = array_merge($results[2]->toArray(false), $results[3]->toArray(false));
    
    //Merge the default group role-permissions with the default group-permissions.
    $default_group_permissions = array_merge($results[4]->toArray(false), $results[5]->toArray(false));
    
    //Merge the user role permissions with the user specific permissions.
    $user_permissions = array_merge($results[6]->toArray(false), $results[7]->toArray(false));
    
    //Set the global permissions.
    foreach($global_permissions as $p){
      $permissions[$p->key] = $p->value;
    }
    
    //Normalize group permissions and check for conflicts.
    $tmp_permissions = [];
    foreach($group_permissions as $p)
    {
      
      //Check if a different group had already defined this permission.
      if(array_key_exists($p->key, $tmp_permissions))
      {
        
        //Check if an ALWAYS/NEVER conflict has already occurred.
        if($tmp_permissions[$p->key] == -3){
          continue;
        }
        
        //Check if we are trying to overwrite a YES/NO conflict with YES or NO.
        elseif(in_array($p->value, [YES, NO]) && $tmp_permissions[$p->key] == -2){
          continue;
        }
        
        //Check if we are trying to overwrite ALWAYS or NEVER with YES or NO. We may not!
        elseif(in_array($tmp_permissions[$p->key], [ALWAYS, NEVER]) && in_array($p->value, [YES, NO])){
          continue;
        }
        
        //Check if an ALWAYS/NEVER conflict occurs.
        elseif(abs($p->value - $tmp_permissions[$p->key]) == 3){
          $tmp_permissions[$p->key] = -3;
          continue;
        }
        
        //Check if a YES/NO conflict occurs.
        elseif(in_array($p->value, [YES, NO]) && abs($p->value - $tmp_permissions[$p->key]) == 1){
          $tmp_permissions[$p->key] = -2;
          continue;
        }
        
      }
      
      //Set.
      $tmp_permissions[$p->key] = $p->value;
      
    }
    
    //Overwrite the global permissions with the normalized group permissions.
    foreach($tmp_permissions as $key => $value)
    {
      
      //Only overwrite if the new value is stronger.
      if(in_array($value, [-2, YES, NO]) && in_array($permissions[$key], [ALWAYS, NEVER])){
        continue;
      }
      
      $permissions[$key] = $value;
      
    }
    
    //Overwrite the combined permission with the values we have in our default_group_permissions.
    foreach($default_group_permissions as $p)
    {
      
      //We can not overwrite ALWAYS or NEVER or ALWAYS/NEVER conflicts with YES or NO.
      if(in_array($p->value, [YES, NO]) && in_array($permissions[$p->key], [-3, ALWAYS, NEVER])){
        continue;
      }
      
      $permissions[$p->key] = $p->value;
      
    }
    
    //Overwrite the combined permissions with user specific permissions.
    foreach($user_permissions as $p){
      $permissions[$p->key] = $p->value;
    }
    
    //Check for errors and cast to booleans.
    foreach($permissions as $key => $value)
    {
      
      //Still a permission conflict? We must ABORT!!
      if($value == -2){
        throw new \exception\Permission(
          'An unresolved YES/NO conflict occurred with the "%s" permission in %s for user %s.',
          $key, $cinfo->title, $user_id
        );
      }
      
      //Still a permission conflict? We must ABORT!!
      elseif($value == -3){
        throw new \exception\Permission(
          'An unresolved ALWAYS/NEVER conflict occurred with the "%s" permission in %s for user %s.',
          $key, $cinfo->title, $user_id
        );
      }
      
      //Cast to boolean.
      $permissions[$key] = ($value > 0);
      
    }
    
    //Return the calculated permissions in an ArrayObject.
    return new \classes\ArrayObject($permissions);
    
  }
  
  //Return the cache as it stands in the database.
  private function readCache($user_id, $component_id)
  {
    
    return tx('Sql')->exe(
      'SELECT * FROM `#system_permission_cache` WHERE `user_id` = ?i AND `component_id` = ?i',
      $user_id, $component_id
    );
    
  }
  
  //Write the database cache.
  private function writeCache($user_id, $component_id, $data)
  {
  
    #TODO: Create the writeCache method.
  
  }
  
}
