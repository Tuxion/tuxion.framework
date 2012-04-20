<?php namespace components\controllers\example;

//trace( $file, $this, $R, $R->is('/example') );

//var_dump($R->is('test'));

$R('test')
  ->pre(function(){
    echo 'hoi';
  });
