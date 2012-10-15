<?php namespace config\constants;

return [
  
  //Request methods.
  'get'    => 1,
  'post'   => 2,
  'put'    => 4,
  'delete' => 8,
  
  //Exception types.
  'ex_error'              => 0b0000000000000000001,
  'ex_exception'          => 0b0000000000000000010,
  'ex_expected'           => 0b0000000000000000100,
  'ex_unexpected'         => 0b0000000000000001000,
  'ex_authorization'      => 0b0000000000000010000,
  'ex_configuration'      => 0b0000000000000100000,
  'ex_emptyresult'        => 0b0000000000001000000,
  'ex_validation'         => 0b0000000000010000000,
  'ex_user'               => 0b0000000000100000000,
  'ex_programmer'         => 0b0000000001000000000,
  'ex_permissionconflict' => 0b0000000010000000000,
  'ex_sql'                => 0b0000000100000000000,
  'ex_connection'         => 0b0000001000000000000,
  'ex_invalidargument'    => 0b0000010000000000000,
  'ex_deprecated'         => 0b0000100000000000000,
  'ex_restriction'        => 0b0001000000000000000,
  'ex_notfound'           => 0b0010000000000000000,
  'ex_filemissing'        => 0b0100000000000000000,
  'ex_inputmissing'       => 0b1000000000000000000,
  
  //Permission values.
  'never'  => -1,
  'no'     => 0,
  'yes'    => 1,
  'always' => 2,
  
  //Query-builder stuff.
  'all'   => 0,
  'a'     => 1,
  'below' => -1,
  'equal' => 0,
  'above' => 1,
  'asc'   => 1,
  'desc'  => -1,
  
  //Common variables.
  'n'  => "\r\n",
  'br' => "<br>\n"
  
];
