<?php namespace classes\data;

class ArrayWrapper extends BaseData implements \IteratorAggregate, \ArrayAccess
{
  
  use \traits\ArrayContainer
  ;#TEMP: causes memory corruption
  //{
  //   set as private _set;
  //   arrayGet as private _arrayGet;
  // }
  
  ##
  ## MAGIC METHODS
  ##
  
  //The constructor accepts the initial array.
  public function __construct($arr=[])
  {
    $this->set($arr);
  }
  
  //Magic get.
  public function __get($key)
  {
    return $this->arrayGet($key);
  }
  
  //Magic set.
  public function __set($key, $value)
  {
    return $this->arraySet($key, $value);
  }
  
  //Magic unset.
  public function __unset($key)
  {
    return $this->arrayUnset($key);
  }
  
  //Magic isset.
  public function __isset($key)
  {
    return $this->offsetExists($key);
  }
  
  
  ##
  ## MUTATION METHODS
  ##
  
  //Cast the array to a string.
  public function toString()
  {
    
    return new StringWrapper('[data\\Array]');
    
  }
  
  //JSON encode and return in a StringWrapper.
  public function toJSON()
  {
    
    $assoc = $this->isAssociative();
    
    return $this
    
    ->map(function($node, $key)use($assoc){
      return ($assoc ? wrap($key)->toJSON().':' : '').$this->wrapRaw($key)->toJSON();
    })
    
    ->join(', ')
    ->prepend($assoc ? '{' : '[')
    ->append($assoc ? '}' : ']');
    
  }
  
  //Return a StringWrapper containing the visual representation of this array.
  public function visualize($short=false)
  {
    
    if($short){
      return new StringWrapper('array('.$this->size().')');
    }
    
    return $this
    
    ->map(function($node, $key){
      return wrap($key)->visualize().' => '.wrap($node)->visualize();
    })
    
    ->join(', ')
    ->prepend('[')
    ->append(']');
    
  }
  
  //Return a new ArrayObject with the keys of this array as values.
  public function keys()
  {
    
    return new self(array_keys($this->arr));
    
  }
  
  //Return a new ArrayObject with the values of this array as values.
  public function values()
  {
    
    return new self(array_values($this->arr));
    
  }
  
  //Return a new ArrayObject by iterating over the data and using the return value from the callback and return it.
  public function map(\Closure $callback)
  {
    
    //Create the new instance and set the counter to zero.
    $return = new self;
    $i = 0;
    
    //Call the given callback for every node, passing its value, key and the counter.
    foreach($this->arr as $key => $value){
      $return->push($callback($value, $key, $i));
      $i++;
    }
    
    //Return the new instance, filled with the return values of the called callbacks.
    return $return;
  
  }
  
  //Return a new ArrayObject filled with the nodes that were at the given key.
  public function pluck()
  {
    
    //Create a new instance.
    $return = new self;
    
    //For every node in our current array.
    foreach($this->arr as $node)
    {
      
      //Grab the sub node of the current node for every argument passed to this function.
      foreach(func_get_args() as $key){
        $node = $node->arrayGet($key);
      }
      
      //Push the last node into the new instance.
      $return->push($node);
      
    }
    
    //Return the new instance.
    return $return;
    
  }
  
  //Return a new ArrayObject, excluding the nodes that were not in the given keys.
  public function having(array $keys)
  {
    
    //Create a new instance.
    $return = new self;
    
    //For every given key.
    foreach($keys as $key1 => $key2)
    {
      
      //If a new key name has been given.
      if(is_string($key1)){
        $return->arraySet($key1, $this->arrayGet($key2));
      }
      
      //Use the same key name.
      else{
        $return->arraySet($key2, $this->arrayGet($key2));
      }
      
    }
    
    //Return the new instance.
    return $return;
    
  }
  
  //Returns a new ArrayObject, excluding the nodes that were in the given keys.
  public function without(array $keys)
  {
    
    //Return a new instance with the computed difference in keys between the current array and given keys.
    return new self(array_diff($this->arr, array_fill_keys($keys, null)));
    
  }
  
  //Return a new ArrayObject containing only the nodes that made the given callback return true.
  public function filter(\Closure $callback)
  {
    
    //Create the new instance.
    $return = new self;
    
    //For every node, call the callback and add the node to the instance of the callback returned true.
    foreach($this->arr as $k => $v){
      if($callback($v, $k) === true){
        $return->arraySet($k, $v);
      }
    }
    
    //Return the new instance.
    return $return;
    
  }
  
  //Returns a slice of the array in the form of a new ArrayObject.
  public function slice($offset=0, $length=null)
  {
    
    //Return a new instance with a slice of the current array.
    return new self(array_slice($this->arr, $offset, $length));
    
  }
  
  //Flatten the array and return a flat new ArrayObject.
  public function flatten()
  {
    
    $output = [];
    $input = $this->toArray();
    
    array_walk_recursive($input, function($a)use(&$output){
      $output[] = $a;
    });
    
    return new self($output);
    
  }
  
  //Boils down the array of values into a single value.
  //reduce([int $mode = LEFT, ]callable $callback[, $initial = null]);
  public function reduce()
  {
    
    //Handle arguments.
    $args = func_get_args();
    
    //Get mode.
    $mode = ((is_int($args[0])) ? array_shift($args) : LEFT);
    
    //Validate remaining arguments.
    if(count($args) < 1 || !is_callable($args[0])){
      throw new \exception\InvalidArgument('No callback given.');
    }
    
    //Get callback.
    $callback = array_shift($args);
    
    //Get initial output value.
    $output = ((count($args) > 0) ? array_shift($args) : null);
    
    //Get array.
    $array = ($mode < 0 ? $this->arr : array_reverse($this->arr));
    
    //Iterate.
    foreach($array as $key => $value){
      $output = $callback($output, $value, $key);
    }
    
    //Return the wrapped output.
    return wrap($output);
    
  }
  
  //Returns a string created of all nodes converted to string and joined together by the given separator.
  public function join($separator='')
  {
    
    return new StringWrapper(implode($separator, $this->arr));
    
  }
  
  //Like join, instead of using just the values concatenates the key and value together using the [delimiter].
  public function joinWithKeys($delimiter, $separator)
  {
    
    $implode = '';
    $array = $this->arr;
    
    for($i=1, $size = count($array); list($key, $value) = each($array), $i <= $size; $i++){
      $implode .= "$key$separator$value".($i<$size?$delimitter:'');
    }
    
    return new StringWrapper($implode);
    
  }
  
  //Return the key at the first node with the given value.
  public function search($value, $strict = false)
  {
    
    return wrap(array_search($value, $this->arr, $strict));
    
  }
  
  //Recursively search through the array and return an array of keys, leading to the first match found.
  public function searchRecursive($needle, $offset=0, $strict=false)
  {
    
    //Create the iterator closure.
    $iterator = function($haystack, $depth = 0)use(&$needle, &$offset, &$strict, &$iterator){
      
      //Iterate over the haystack.
      foreach($haystack AS $key => $value)
      {
        
        //If the value is an array.
        if(is_array($value))
        {
          
          //Iterate over the sub-nodes.
          $keys = $iterator($value, ($depth+1));
          
          //If no match was found, continue to the next iteration.
          if(empty($keys)){
            continue;
          }
          
          //A match was found. Add our own key and cancel the iteration. We're done.
          array_unshift($keys, $key);
          break;
          
        }
        
        //Match the values if we're passed our offset depth.
        if(($depth >= $offset) && ($strict === true ? ($value === $needle) : ($value == $needle))){
          $keys = [$key];
          break;
        }
        
      }
      
      //Return the keys.
      return isset($keys) ? $keys : [];
      
    };
    
    //Return the wrapped result of the iterator.
    return wrap($iterator($this->arr));
    
  }
  
  
  ##
  ## GETTERS
  ##
  
  //Alias for toArray(false).
  public function get()
  {
    
    return $this->toArray(false);
    
  }
  
  //Return the node under [key] wrapped in a new Data object.
  public function wrap($key)
  {
    
    //Return undefined if the node does not exist.
    if(!$this->arrayExists($key)){
      return new Undefined;
    }
    
    //Return the node.
    return wrap($this->arrayGet($key));
    
  }
  
  //Return the node under [key] if it was wrapped, otherwise wrap it first.
  public function wrapRaw($key)
  {
    
    //Return undefined if the node does not exist.
    if(!$this->arrayExists($key)){
      return new Undefined;
    }
    
    //Return the node.
    return wrapRaw($this->arrayGet($key));
    
  }
  
  //Return the wrapped alternative if this array is empty.
  public function alt($alternative)
  {
    
    return ($this->isEmpty() ? wrap($alternative) : $this);
    
  }
  
  
  ##
  ## INFORMATION METHODS
  ##
  
  //Return false if all keys are numeric.
  public function isAssociative()
  {
    
    return ! $this->every(function($val, $key){
      return is_numeric($key);
    });
    
  }
  
  
  ##
  ## ARRAY METHODS
  ##
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetGet($key)
  {
    return $this->arrayGet($key);
  }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetSet($key, $val)
  {
    return $this->arraySet($key, $val);
  }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetExists($key)
  {
    return $this->arrayExists($key);
  }
  
  //Semi-magic method implemented by \ArrayAccess.
  public function offsetUnset($key)
  {
    return $this->arrayUnset($key);
  }
  
  //Semi-magic method implemented by \IteratorAggregate.
  public function getIterator()
  {
    return new \ArrayIterator($this->arr);
  }
  
  #TEMP: causes memory corruption.
  // //Extend the ArrayContainer
  // public function set($arr)
  // {
    
  //   if($arr instanceof self){
  //     $arr = $arr->arr;
  //   }
    
  //   if(!is_array($arr)){
  //     throw new \exception\InvalidArgument('Expecting an array or ArrayObject. %s given.', typeof());
  //   }
    
  //   $this->_set($arr);
    
  // }
  
}
