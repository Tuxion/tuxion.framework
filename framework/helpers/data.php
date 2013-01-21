<?php

//Wrap something.
function wrap()
{
  
  $data = (func_num_args() == 1 ? func_get_arg(0) : (func_num_args() > 1 ? func_get_args() : null));
  
  if(is_null($data)){
    return new \classes\data\Undefined;
  }
  
  if(is_array($data)){
    return new \classes\data\ArrayWrapper($data);
  }
  
  if(is_bool($data)){
    return new \classes\data\BooleanWrapper($data);
  }
  
  if(is_string($data)){
    return new \classes\data\StringWrapper($data);
  }
  
  if(is_numeric($data)){
    return new \classes\data\NumberWrapper($data);
  }
  
  if($data instanceof \Closure){
    return new \classes\data\FunctionWrapper($data);
  }
  
  if(is_object($data)){
    return new \classes\data\ObjectWrapper($data);
  }
  
  throw new \exception\NotImplemented('No wrapper implemented for data of type: %s', typeof($data));
  
}

//Create a UrlWrapper.
function url($input)
{
  
  return new \classes\data\UrlWrapper($input);
  
}

//Create an EmailWrapper.
function email($input)
{
  
  return new \classes\data\EmailWrapper($input);
  
}

//Create an IPv4Wrapper.
function ipv4($input)
{
  
  return new \classes\data\IPv4Wrapper($input);
  
}

//Create an IPv6Wrapper.
function ipv6($input)
{
  
  return new \classes\data\IPv6Wrapper($input);
  
}

//Create a PhoneNumberWrapper.
function phone($input)
{
  
  return new \classes\data\PhoneNumberWrapper($input);
  
}

//Create a PathWrapper.
function path($input)
{
  
  return new \classes\data\PathWrapper($input);
  
}

//Create a QueryStringWrapper.
function queryString($input)
{
  
  return new \classes\data\QueryStringWrapper($input);
  
}

//Only wrap raw input.
function wrapRaw($input)
{
  
  if(is_wrapped($input)){
    return clone $input;
  }
  
  return wrap($input);
  
}

function is_wrapped($data)
{
  
  return ($data instanceof \classes\data\BaseData);
  
}

function unwrap($data)
{
  
  if($data instanceof \classes\data\BaseData){
    return $data->get();
  }
  
  return $data;
  
}

function raw(
  &$v0=null, &$v1=null, &$v2=null, &$v3=null, &$v4=null,
  &$v5=null, &$v6=null, &$v7=null, &$v8=null, &$v9=null
){
  
  if(func_num_args() > 10){
    throw new \exception\Restriction('HAHA! You can only extract raw() values from 10 variables at a time.');
  }
  
  $v0 = unwrap($v0);
  $v1 = unwrap($v1);
  $v2 = unwrap($v2);
  $v3 = unwrap($v3);
  $v4 = unwrap($v4);
  $v5 = unwrap($v5);
  $v6 = unwrap($v6);
  $v7 = unwrap($v7);
  $v8 = unwrap($v8);
  $v9 = unwrap($v9);
  
}
