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
  'ex_sql' => 512,
  'ex_connection' => 1024,
  'ex_invalidargument' => 2048,
  'ex_deprecated' => 4096,
  'ex_restriction' => 8192,
  'ex_notfound' => 16384,
  'ex_filemissing' => 32768,
  'ex_inputmissing' => 65536
  
];