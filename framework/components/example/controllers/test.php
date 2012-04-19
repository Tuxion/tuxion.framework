<?php namespace components\controllers\example;

//trace( $file, $this->getPath(), $R, $R->is('$test/foo') );

$R->with('nyerk', function(){
  
  trace($this->is('foo'));
  
});

$R->with('test', function(){
  
  echo 'You are home!';
  
});
