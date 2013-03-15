<?php namespace config\constants;

return [
  
  //Request methods.
  'get'    => 1,
  'post'   => 2,
  'put'    => 4,
  'delete' => 8,
  
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
  'left'  => -1,
  'both'  => 0,
  'right' => 1,
  
  //Common variables.
  'n'  => "\r\n",
  'br' => "<br>\n",
  'st' => true
  
];
