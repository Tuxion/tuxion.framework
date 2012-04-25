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

$R('/ $example2');

$R('test')
  ->pre('Rerouting to someplace else.', function(){
    $this->reroute('nyerk/foo/bar');
  });

$R->with('nyerk/foo', function(){
  
  $this('bar')->end('Display the right bar.', function(){
    echo 'Hallo!';
  });
  
});
