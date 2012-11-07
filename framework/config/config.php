<?php namespace config\config;

return [
  
  '*' => [
    
    //Debug.
    'debug' => true,
    'logging' => null,          //When set to null, debug will be used.
    'log_messages' => true,    //true = all, false = none.
    'log_exceptions' => true,       //true = all, false = none, [array] = a list of exception types, for example: ['Error', 'InternalServerError']
    'log_exception_caught' => true,
    'log_file' => 'error.log',  //Anything that evaluates to empty will cause the apache error.log to be used. Otherwise a string may be used as file-path.
    
    //Website.
    'title' => 'Powered by Tuxion Framework',
    'description' => 'A web-application running on a fresh installation of Tuxion Framework!',
    
    //Permissions.
    'permission_caching' => true,
    
    //Router.
    'route_allow_numeric_components' => true
    
  ],
  
  'localhost' => [
    'config_table' => '#system_config',
    'config_key' => 'key',
    'config_value' => 'value'
  ]
  
];
