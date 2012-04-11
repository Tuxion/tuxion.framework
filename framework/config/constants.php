<?php namespace config\constants;

return [
  
  //Request methods.
  'get' => 1,
  'post' => 2,
  'put' => 4,
  'delete' => 8,
  
  //Exception types.
  'ex_error' => 1,
  'ex_exception' => 2,
  'ex_expected' => 4,
  'ex_unexpected' => 8,
  'ex_authorisation' => 16,
  'ex_emptyresult' => 32,
  'ex_validation' => 64,
  'ex_user' => 128,
  'ex_programmer' => 256,
  'ex_permissionconflict' => 512,
  'ex_sql' => 1024,
  'ex_connection' => 2048,
  'ex_invalidargument' => 4096,
  'ex_deprecated' => 8192,
  'ex_restriction' => 16384,
  'ex_notfound' => 32768,
  'ex_filemissing' => 65536,
  'ex_inputmissing' => 131072,
  
  //Permission values.
  'never' => -1,
  'no' => 0,
  'yes' => 1,
  'always' => 2
  
];
