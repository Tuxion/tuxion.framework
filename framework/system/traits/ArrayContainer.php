<?php namespace traits;

trait ArrayContainer
{
  
  private
    $arr_permissions=7,
    $arr=[];
  
  //Itterate over the array.
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
  
  //Create a new self by itterating over the data and using the return value from the callback and return it.
  public function map(\Closure $callback)
  {
  
    $r = new \classes\Data;
    $i = 0;
    
    foreach($this->arr as $key => $value){
      $r->push($callback($value, $key, $i));
      $i++;
    }
    
    return $r;
  
  }
  
  //Return the arr.
  public function toArray()
  {
    
    $arr = [];
    
    foreach($this->arr as $key => $value)
    {
    
      if(uses($value, 'ArrayContainer')){
        $value = $value->toArray();
      }
      
      $arr[$key] = $value;
      
    }
    
    return $arr;
    
  }
  
  //Push a new value into the array.
  public function push()
  {
    
    if(func_num_args() == 1){
      $this->arr[] = func_get_arg(0);
    }
    
    elseif(func_num_args() == 2){
      $this->arr[func_get_arg(0)] = func_get_arg(1);
    }
    
    else{
      throw new \exception\InvalidArguments('Expecting one or two arguments. %s Given.', func_num_args());
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
  
  //Permission setter.
  protected function setArrayPermissions($read = true, $write = true, $delete = true)
  {
    $this->arr_permissions = ($read ? 1 : 0) | ($write ? 2 : 0) | ($delete ? 4 : 0);
    return $this;
  }
  
  //Native getter.
  private function arrayGet($key)
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
  private function arraySet($key, $value)
  {
    
    if( ! $this->arrayPermission(2)){
      throw new \exception\Restriction('You do not have write permissions.');
    }
    
    return $this->arr[$key] = $value;
    
  }
  
  //Native unsetter.
  private function arrayUnset($key)
  {
    
    if( ! $this->arrayPermission(4)){
      throw new \exception\Restriction('You do not have delete permissions.');
    }
    
    unset($this->arr[$key]);
    
    return $this;
    
  }
  
  //Permission check.
  private function arrayPermission($int)
  {
    return checkbit($int, $this->arr_permissions);
  }
  
}