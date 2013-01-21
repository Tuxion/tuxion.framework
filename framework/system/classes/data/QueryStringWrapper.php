<?php namespace classes\data;

class QueryStringWrapper extends StringWrapper
{
  
  //Protected properties.
  protected
    $parsed=[];
  
  //Return all parsed data or a key from it.
  public function getData($key=null)
  {
    
    //Pre-parse the query string.
    $this->parseQueryString();
    
    //Return all of it?
    if(is_null($key)){
      return new ArrayWrapper($this->parsed);
    }
    
    //Return an Undefined?
    if(!array_key_exists($key, $this->parsed)){
      return new Undefined;
    }
    
    //Return a parsed value.
    return wrapRaw($this->parsed[$key]);
    
  }
  
  //Parse the query string and cache its result.
  private function parseQueryString()
  {
    
    //Do nothing?
    if(count($this->parsed)){
      return $this;
    }
    
    //Do the parsing.
    parse_str($this->value, $this->parsed);
    
    //Enable chaining.
    return $this;
    
  }
  
}
