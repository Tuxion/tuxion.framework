<?php

function Data(){
  
  $data = (func_num_args() == 1 ? func_get_arg(0) : (func_num_args() > 1 ? func_get_args() : null));
  
  if(is_data($data)){
    return $data;
  }
  
  if(is_array($data)){
    return new \classes\DataBranch($data);
  }
  
  return new \classes\DataLeaf($data);
  
}

function is_data($data){
  return uses($data, 'Data');
}

function data_of($data){
  
  if($data instanceof \classes\DataBranch){
    return $data->asArray();
  }
  
  if($data instanceof \classes\DataLeaf){
    return $data->get();
  }
  
  return $data;
  
}

function raw(&$v0, &$v1=null, &$v2=null, &$v3=null, &$v4=null, &$v5=null, &$v6=null, &$v7=null, &$v8=null, &$v9=null){
  
  if(func_num_args() > 10){
    throw new \exception\InvalidArgument('HAHA! You can only extract raw() values of 10 variables at a time.');
  }
  
  $v0 = data_of($v0);
  $v1 = data_of($v1);
  $v2 = data_of($v2);
  $v3 = data_of($v3);
  $v4 = data_of($v4);
  $v5 = data_of($v5);
  $v6 = data_of($v6);
  $v7 = data_of($v7);
  $v8 = data_of($v8);
  $v9 = data_of($v9);
  
}
