<?php

//Set or invoke a route object.
function route(){
  
  static $c=null;
  
  if(func_num_args() == 0){
    return $c;
  }
  
  if(func_num_args() == 1
    && ( func_get_arg(0) instanceof \classes\Controller
      || func_get_arg(0) instanceof \classes\ComponentController
      || is_null(func_get_arg(0))
    )
  ){
    return $c = func_get_arg(0);
  }
  
  if(is_null($c)){
    throw new \exception\Restriction('You can not use the c function here.');
  }
  
  return call_user_func_array([$c, 'getSubController'], func_get_args());
  
}

function url(){
  return call_user_func_array('classes\\Url::create', func_get_args());
}

function set_status_header($code=200, $text=null){
  
  $stati = [
    
    200	=> 'OK',
    201	=> 'Created',
    202	=> 'Accepted',
    203	=> 'Non-Authoritative Information',
    204	=> 'No Content',
    205	=> 'Reset Content',
    206	=> 'Partial Content',

    300	=> 'Multiple Choices',
    301	=> 'Moved Permanently',
    302	=> 'Found',
    304	=> 'Not Modified',
    305	=> 'Use Proxy',
    307	=> 'Temporary Redirect',

    400	=> 'Bad Request',
    401	=> 'Unauthorized',
    403	=> 'Forbidden',
    404	=> 'Not Found',
    405	=> 'Method Not Allowed',
    406	=> 'Not Acceptable',
    407	=> 'Proxy Authentication Required',
    408	=> 'Request Timeout',
    409	=> 'Conflict',
    410	=> 'Gone',
    411	=> 'Length Required',
    412	=> 'Precondition Failed',
    413	=> 'Request Entity Too Large',
    414	=> 'Request-URI Too Long',
    415	=> 'Unsupported Media Type',
    416	=> 'Requested Range Not Satisfiable',
    417	=> 'Expectation Failed',

    500	=> 'Internal Server Error',
    501	=> 'Not Implemented',
    502	=> 'Bad Gateway',
    503	=> 'Service Unavailable',
    504	=> 'Gateway Timeout',
    505	=> 'HTTP Version Not Supported'
    
  ];
  
  if(!array_key_exists($code, $stati)){
    throw new \exception\InvlaidArgument(
      'Invalid status code "%s" given. Valid status codes are: %s.',
      $code, implode(', ', array_keys($stati))
    );
  }
  
  if(!is_string($text)){
    $text = $stati[$code];
  }

  $server_protocol = tx('Server')->server_protocol;

  if(substr(php_sapi_name(), 0, 3) == 'cgi'){
    header("Status: $code $text", true);
  }
  
  elseif($server_protocol == 'HTTP/1.1' || $server_protocol == 'HTTP/1.0'){
    header("$server_protocol $code $text", true, $code);
  }
  
  else{
    header("HTTP/1.1 $code $text", true, $code);
  }
  
}

function uses($class, $trait_name){
  
  //We must do this, because otherwise "class_uses" will attempt to autoload the class.
  if(!is_object($class)){
    return false;
  }
  
  do{
    if(array_key_exists("traits\\$trait_name", class_uses($class))){
      return true;
    }
  }
  while($class = get_parent_class($class));
  
  return false;
  
}

