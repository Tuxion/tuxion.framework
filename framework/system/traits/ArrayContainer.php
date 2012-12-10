<?php namespace traits;

trait ArrayContainer
{
  
  private
    $arr_permissions=7,
    $arr=[];
  
  //Destroy the children when the parent dies.
  public function __destruct()
  {
    
    foreach($this->arr as $key => $node){
      unset($this->arr[$key]);
    }
    
  }
  
  
  ##
  ## GETTERS
  ##
    
  //Return the node that is present at given $index.
  public function idx($index)
  {
    
    if($index < 0) $index = $this->size() + $index;
    if($index < 0) $index = 0;

    if($this->isEmpty()){
      throw new \exception\Restriction('Can not call ->idx() on empty ArrayContainer.');
    }
    
    $tmp = $this->arr;
      
    reset($tmp);
    $i = 0;

    do{
      if($i < $index){
        $i++;
      }else{
        return current($tmp);
      }
    }
    
    while($i <= $index && next($tmp));    
    
  }
  
  //Extract a sub-node based on the given argument.
  public function extract()
  {
    
    //Handle arguments.
    $key = (func_num_args() == 1
      ? func_get_arg(0)
      : (func_num_args() > 1
        ? func_get_args()
        : null
      )
    );
    
    //Get a node with given key.
    if(is_scalar($key)){
      return $this->arrayGet($key);
    }
    
    //Set the initial return value.
    $return = $this;
    
    //Get a node from the return value, and set that as the new return value.
    foreach($key as $k)
    {
      
      //Get node from ArrayContainer.
      if(uses($return, 'ArrayContainer')){
        $return = $return->arrayGet($k);
        continue;
      }
      
      //Get node from array.
      if(is_array($return)){
        $return = $return[$k];
        continue;
      }
      
      //Get node from something else.
      throw new \exception\InternalServerError(
        'Tried to get a node from a(n) %s using the extract method.', typeof($return)
      );
      
    }
    
    //Return the latest return value.
    return $return;
      
  }
  
  //Return the array and optionally any of the arrays in the sub-nodes.
  public function toArray($recursive = true)
  {
    
    $arr = [];
    
    foreach($this->arr as $key => $value)
    {
      
      if($recursive && uses($value, 'ArrayContainer')){
        $value = $value->toArray();
      }
      
      $arr[$key] = $value;
      
    }
    
    return $arr;
    
  }
  
  
  ##
  ## SETTERS
  ##
  
  //Set the entire array.
  public function set(array $arr)
  {
    
    $this->arr = [];
    
    foreach($arr as $key => $value){
      $this->arraySet($key, $value);
    }
    
    return $this;
    
  }
  
  //Merge one or more given arrays with arr.
  public function merge()
  {
    
    //Loop arguments.
    foreach($arrays as $array)
    {
      
      //Cast ArrayContainers to arrays.
      if(uses($array, 'ArrayContainer')){
        $array = $array->toArray(false);
      }
      
      //Loop the array.
      foreach($array as $key => $value){
        $this->arraySet($key, $value);
      }
      
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Adds one or more given arrays tot this array.
  public function concat()
  {
    
    //Get the used keys.
    $used = array_keys($this->arr);
    
    //Get the currently highest key.
    $i = max(array_filter($used, function($v){return is_numeric($v);})) + 1;
    
    //Loop arguments.
    foreach($arrays as $array)
    {
      
      //Cast ArrayContainers to arrays.
      if(uses($array, 'ArrayContainer')){
        $array = $array->toArray(false);
      }
      
      //Loop the array.
      foreach($array as $key => $value)
      {
        
        //Check if the key is already used.
        if(in_array($key, $used)){
          $used[] = $key = $i++;
        }
        
        //Set the value.
        $this->arraySet($key, $value);
        
      }
      
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Push a new value into the array.
  public function push()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Were enough arguments given?
    if(count($args) < 1){
      throw new \exception\InvalidArgument('Expecting at least one argument. None given.');
    }
    
    //Get the value and key.
    $value = array_pop($args);
    $key = (empty($args) ? null : array_pop($args));
    
    //Calculate a key if none was given.
    if(is_null($key))
    {
      
      //Get all the numeric array keys.
      $keys = array_filter(array_keys($this->arr), function($var){
        return is_numeric($var);
      });
      
      //Make a new key based on that.
      $key = (empty($keys) ? 0 : max($keys)+1);
      
    }
    
    //Set.
    $this->arraySet($key, $value);
    
    return $this;
    
  }
  
  //Unset nodes the given key(s).
  public function remove()
  {
    
    $keys = (func_num_args() == 1 ? func_get_arg(0) : (func_num_args() > 1 ? func_get_args() : null));
    
    if(is_scalar($keys)){
      return $this->arrayUnset($keys);
    }
    
    foreach($keys as $key){
      $this->arrayUnset($key);
    }
    
    return $this;
    
  }
  
  
  ##
  ## ITERATORS
  ##
  
  //Iterate over the array.
  public function each(\Closure $callback)
  {
    
    $i = 0;
    foreach($this->arr as $key => $value)
    {
      
      $r = $callback($value, $key, $i);
      
      if($r === false){
        break;
      }
      
      $i++;
      
    }
    
    return $this;
    
  }
  
  //Iterate over the array and it's sub-arrays.
  public function walk(\Closure $callback)
  {
    
    $walker = function($nodes) use (&$walker, $callback)
    {
    
      $delta = 0;
      
      do
      {
      
        $key = key($nodes);
        $node = current($nodes);
        $delta = ($delta==0 && uses($node, 'ArrayContainer') ? 1 : $delta);
        $c = $callback->bindTo($node);
        $c($delta);
      
        if(uses($node, 'ArrayContainer') && $delta >= 0){
          $walker($node->toArray(false));
          $delta = -1;
          continue;
        }
        
        $delta = 0;
        if(next($nodes)===false) break;
      
      }
      while(true);
    
    };
    
    $walker($this->arr);
    
    return $this;
    
  }
  
  
  ##
  ## INFORMATION
  ##
  
  //Return the length of the array.
  public function length()
  {
    return count($this->arr);
  }
  
  //Alias for length()
  public function num()
  {
    return $this->length();
  }
  
  //Alias for length()
  public function size()
  {
    return $this->length();
  }
  
  //Alias for length()
  public function count()
  {
    return $this->length();
  }
  
  //Returns true if one of the nodes in the array has the given value.
  public function has($value, $strict=false)
  {
    
    return $this->keyOf($value, $strict) !== false;
    
  }
  
  //Returns true for empty nodes.
  public function isEmpty()
  {
    
    return empty($this->arr);
    
  }
  
  //Returns true if the given offset exists in this array.
  public function arrayExists($key)
  {
    
    return array_key_exists($key, $this->arr);
    
  }
  
  //Returns the key of the first element in this array with the given value.
  public function keyOf($value, $strict=false)
  {
    
    foreach($this->arr as $key => $val){
      if($strict ? $val === $value : $val == $value){
        return true;
      }
    }
    
    return false;
    
  }
  
  
  ##
  ## NATIVE CODE
  ##
  
  //Native getter.
  public function arrayGet($key)
  {
    
    if( ! $this->arrayPermission(1)){
      throw new \exception\Restriction('You do not have read permissions.');
    }
    
    if( ! array_key_exists($key, $this->arr)){
      throw new \exception\NonExistent('Could not find "%s" in %s.', $key, get_class($this));
    }
    
    return $this->arr[$key];
    
  }
  
  //Native setter.
  public function arraySet($key, $value)
  {
    
    if( ! $this->arrayPermission(2)){
      throw new \exception\Restriction('You do not have write permissions.');
    }
    
    return $this->arr[$key] = $value;
    
  }
  
  //Native unsetter.
  public function arrayUnset($key)
  {
    
    if( ! $this->arrayPermission(4)){
      throw new \exception\Restriction('You do not have delete permissions.');
    }
    
    unset($this->arr[$key]);
    
    return $this;
    
  }
  
  
  ##
  ## PERMISSIONS
  ##
  
  //Permission setter.
  public function setArrayPermissions($read = true, $write = true, $delete = true)
  {
    
    $this->arr_permissions = ($read ? 1 : 0) | ($write ? 2 : 0) | ($delete ? 4 : 0);
    return $this;
    
  }
  
  //Permission check.
  public function arrayPermission($int)
  {
    
    return checkbit($int, $this->arr_permissions);
    
  }
  
}
