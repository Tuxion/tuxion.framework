<?php namespace components\controllers\example;

//trace( $file, $this, $R, $R->is('/example') );

// trace($R);
// trace($R);
// trace($R);
// trace($R);
// trace($R);

// // $R('test')
// //    ->end(function(){
// //      echo 'hoi';
// //    });

$R('test')
  ->end('hoi', function(){
    echo 'bla';
  });
