<?php namespace components\controllers\example;

//trace( $file, $this, $R, $R->is('/example') );

$R('test')
   ->end(function(){
     echo 'hoi';
   });
