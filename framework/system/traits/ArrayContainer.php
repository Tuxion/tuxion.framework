<?php namespace traits;

trait ArrayContainer
{
  
  //Returns true if the given argument is an array, or uses this arrayContainer trait.
  public static function isArray($mixed)
  {
    
    return (is_array($mixed) || uses($mixed, 'ArrayContainer'));
    
  }
  
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
    
  //Return the node that is present at given $index.
  public function idx($index)
  {
    
    if($index < 0) $index = $this->size() + $index;
    if($index < 0) $index = 0;

    if($this->isEmpty()){
      throw new \exception\Programmer('The array is empty.');
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
  
  //Return a new $this by iterating over the data and using the return value from the callback and return it.
  public function map(\Closure $callback, $blank=false)
  {
  
    $r = ($blank ? new \classes\ArrayObject : new $this);
    $i = 0;
    
    foreach($this->arr as $key => $value){
      $r->push($callback($value, $key, $i));
      $i++;
    }
    
    return $r;
  
  }
  
  //Return a new ArrayObject filled with the nodes that were at the given key.
  public function pluck()
  {
    
    $return = new \classes\ArrayObject;
    
    foreach($this->arr as $node)
    {
      
      foreach(func_get_args() as $key){
        $node = $node->arrayGet($key);
      }
      
      $return->push($node);
      
    }
    
    return $return;
    
  }
  
  //Return a new DataBranch, excluding the nodes that were not in the given keys.
  public function having(array $keys, $blank=false)
  {
    
    $return = ($blank ? new \classes\ArrayObject : new $this);
    
    foreach($keys as $key1 => $key2)
    {
      
      if(is_string($key1)){
        $return->arraySet($key1, $this->arrayGet($key2));
      }
      
      else{
        $return->arraySet($key2, $this->arrayGet($key2));
      }
      
    }
    
    return $return;
    
  }
  
  //Return a new DataBranch containing only the nodes that made the given callback return true.
  public function filter(\Closure $callback, $blank=false)
  {
    
    $return = ($blank ? new \classes\ArrayObject : new $this);
    
    foreach($this->arr as $k => $v){
      if($callback($v, $k) === true){
        $return->arraySet($k, $v);
      }
    }
    
    return $return;
    
  }
  
  //Returns a string created of all child-nodes converted to string and joined together by the given $separator.
  public function join($separator='')
  {
    
    $return = '';
    $s = '';
    
    foreach($this->arr as $value){
      $return .= $s . $value;
      $s = $separator;
    }
    
    return $return;
    
  }
  
  //Returns a slice of the array in the form of a new $this.
  public function slice($offset=0, $length=null, $blank=false)
  {
    
    $r = ($blank ? new \classes\ArrayObject : new $this);
    $r->set(array_slice($this->arr, $offset, $length));
    return $r;
    
  }
  
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
    
    //Get the given arrays.
    $arrays = array_filter(func_get_args(), function($v){return self::isArray($v);});

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
    
    //Get the given arrays.
    $arrays = array_filter(func_get_args(), function($v){return self::isArray($v);});
    
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
  
  //Flatten the array and return a flat new self.
  public function flatten($blank=false)
  {
    
    $return = ($blank ? new \classes\ArrayObject : new $this);
    
    $return->set(array_flatten($this->arr));
    
    return $return;
    
  }
  
  //Return the arr and optionally any of the arr's in the sub-nodes.
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
  
  //Return a new ArrayObject with the keys of this array as values.
  public function keys()
  {
    
    return new \classes\ArrayObject(array_keys($this->arr));
    
  }
  
  //Return a new ArrayObject with the values of this array as values.
  public function values()
  {
    
    return new \classes\ArrayObject(array_values($this->arr));
    
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
  
  //Permission setter.
  public function setArrayPermissions($read = true, $write = true, $delete = true)
  {
    
    $this->arr_permissions = ($read ? 1 : 0) | ($write ? 2 : 0) | ($delete ? 4 : 0);
    return $this;
    
  }
  
  //Native getter.
  public function arrayGet($key)
  {
    
    if( ! $this->arrayPermission(1)){
      throw new \exception\Restriction('You do not have read permissions.');
    }
    
    if( ! array_key_exists($key, $this->arr)){
      throw new \exception\NotFound('Could not find "%s" in %s.', $key, get_class($this));
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
  
  //Returns true if the given offset exists in this array.
  public function arrayExists($key)
  {
    
    return array_key_exists($key, $this->arr);
    
  }
  
  //Permission check.
  public function arrayPermission($int)
  {
    
    return checkbit($int, $this->arr_permissions);
    
  }
  
  //Returns true for empty nodes.
  public function isEmpty()
  {
    
    return empty($this->arr);
    
  }
  
}
