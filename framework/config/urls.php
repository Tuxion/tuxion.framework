<?php namespace config\urls;

$domain = '192.168.1.100';
$path = 'tuxion.framework';
$base = $domain.($path ? "/$path" : '');

return [
  'domain' => $domain,
  'path' => $path,
  'base' => $base
];
