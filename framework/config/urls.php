<?php namespace config\urls;

$domain = 'localhost';
$path = 'tuxion.framework';
$base = $domain.($path ? "/$path" : '');

return [
  'domain' => $domain,
  'path' => $path,
  'base' => $base
];
