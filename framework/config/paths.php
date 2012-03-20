<?php namespace config\paths;

$base = realpath(dirname(__FILE__).'/../');

return [
  'base' => $base,
  'logs' => "$base/logs",
  'plugins' => "$base/plugins",
  'components' => "$base/components",
  'core' => "$base/system/core",
  'classes' => "$base/system/classes",
  'exceptions' => "$base/system/exceptions",
  'templates' => "$base/templates",
  'themes' => "$base/themes",
  'root' => realpath("$base/../")
];